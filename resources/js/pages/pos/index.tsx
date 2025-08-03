import React, { useState } from 'react';
import { AppShell } from '@/components/app-shell';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { router } from '@inertiajs/react';
import { formatCurrency } from '@/lib/utils';

interface Product {
    id: number;
    name: string;
    sku: string;
    price: number;
    stock_quantity: number;
    category: string;
    is_low_stock: boolean;
}

interface Customer {
    id: number;
    name: string;
    email: string;
    loyalty_points: number;
}

interface Promotion {
    id: number;
    name: string;
    code: string;
    type: string;
    value: number;
    minimum_purchase: number | null;
    is_valid: boolean;
}

interface CartItem {
    product: Product;
    quantity: number;
    unit_price: number;
    discount_amount: number;
    total_price: number;
}

interface Props {
    products: Product[];
    customers: Customer[];
    promotions: Promotion[];
    lowStockProducts: number;
    recentSales: Array<{
        id: number;
        transaction_number: string;
        total_amount: number;
        created_at: string;
    }>;
    todayStats: {
        sales_count: number;
        sales_total: number;
        customers_served: number;
    };
    [key: string]: unknown;
}

export default function PosIndex({ 
    products, 
    customers, 
    promotions, 
    lowStockProducts, 
    todayStats 
}: Props) {
    const [cart, setCart] = useState<CartItem[]>([]);
    const [selectedCustomer, setSelectedCustomer] = useState<Customer | null>(null);
    const [promotionCode, setPromotionCode] = useState('');
    const [paymentMethod, setPaymentMethod] = useState<'cash' | 'card' | 'digital'>('cash');
    const [amountPaid, setAmountPaid] = useState<string>('');
    const [searchProduct, setSearchProduct] = useState('');
    const [appliedPromotion, setAppliedPromotion] = useState<Promotion | null>(null);

    const filteredProducts = products.filter(product =>
        product.name.toLowerCase().includes(searchProduct.toLowerCase()) ||
        product.sku.toLowerCase().includes(searchProduct.toLowerCase())
    );

    const addToCart = (product: Product) => {
        const existingItem = cart.find(item => item.product.id === product.id);
        
        if (existingItem) {
            if (existingItem.quantity < product.stock_quantity) {
                setCart(cart.map(item =>
                    item.product.id === product.id
                        ? {
                            ...item,
                            quantity: item.quantity + 1,
                            total_price: (item.quantity + 1) * item.unit_price - item.discount_amount
                        }
                        : item
                ));
            }
        } else {
            setCart([...cart, {
                product,
                quantity: 1,
                unit_price: product.price,
                discount_amount: 0,
                total_price: product.price
            }]);
        }
    };

    const removeFromCart = (productId: number) => {
        setCart(cart.filter(item => item.product.id !== productId));
    };

    const updateQuantity = (productId: number, quantity: number) => {
        if (quantity === 0) {
            removeFromCart(productId);
            return;
        }

        setCart(cart.map(item =>
            item.product.id === productId
                ? {
                    ...item,
                    quantity,
                    total_price: quantity * item.unit_price - item.discount_amount
                }
                : item
        ));
    };

    const subtotal = cart.reduce((sum, item) => sum + item.total_price, 0);
    const discountAmount = appliedPromotion 
        ? appliedPromotion.type === 'percentage' 
            ? subtotal * (appliedPromotion.value / 100)
            : appliedPromotion.value
        : 0;
    const taxAmount = (subtotal - discountAmount) * 0.1; // 10% tax
    const totalAmount = subtotal - discountAmount + taxAmount;
    const changeAmount = Math.max(0, parseFloat(amountPaid) - totalAmount);

    const applyPromotion = () => {
        const promotion = promotions.find(p => p.code === promotionCode && p.is_valid);
        if (promotion && (!promotion.minimum_purchase || subtotal >= promotion.minimum_purchase)) {
            setAppliedPromotion(promotion);
        }
    };

    const removePromotion = () => {
        setAppliedPromotion(null);
        setPromotionCode('');
    };

    const processSale = () => {
        if (cart.length === 0) return;
        if (parseFloat(amountPaid) < totalAmount) return;

        const saleData = {
            customer_id: selectedCustomer?.id,
            items: cart.map(item => ({
                product_id: item.product.id,
                quantity: item.quantity,
                unit_price: item.unit_price,
                discount_amount: item.discount_amount,
                total_price: item.total_price
            })),
            subtotal,
            discount_amount: discountAmount,
            tax_amount: taxAmount,
            total_amount: totalAmount,
            payment_method: paymentMethod,
            amount_paid: parseFloat(amountPaid),
            change_amount: changeAmount,
            promotion_code: appliedPromotion?.code
        };

        router.post('/pos', saleData);
    };

    const clearCart = () => {
        setCart([]);
        setSelectedCustomer(null);
        setPromotionCode('');
        setAppliedPromotion(null);
        setAmountPaid('');
    };

    return (
        <AppShell>
            <div className="flex h-screen bg-gray-50">
                {/* Left Panel - Products */}
                <div className="flex-1 p-6 overflow-auto">
                    <div className="mb-6">
                        <h1 className="text-2xl font-bold text-gray-900">💳 Point of Sale</h1>
                        <p className="text-gray-600">Select products to add to cart</p>
                    </div>

                    {/* Today's Stats */}
                    <div className="grid grid-cols-3 gap-4 mb-6">
                        <Card>
                            <CardContent className="p-4">
                                <div className="text-sm text-gray-600">Today's Sales</div>
                                <div className="text-2xl font-bold">{todayStats.sales_count}</div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardContent className="p-4">
                                <div className="text-sm text-gray-600">Revenue</div>
                                <div className="text-2xl font-bold">{formatCurrency(todayStats.sales_total)}</div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardContent className="p-4">
                                <div className="text-sm text-gray-600">Customers</div>
                                <div className="text-2xl font-bold">{todayStats.customers_served}</div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Search Products */}
                    <div className="mb-4">
                        <Input
                            placeholder="🔍 Search products by name or SKU..."
                            value={searchProduct}
                            onChange={(e) => setSearchProduct(e.target.value)}
                            className="w-full"
                        />
                    </div>

                    {/* Low Stock Alert */}
                    {lowStockProducts > 0 && (
                        <div className="mb-4 p-3 bg-orange-100 border border-orange-200 rounded-lg">
                            <div className="text-orange-800">
                                ⚠️ {lowStockProducts} products are running low on stock
                            </div>
                        </div>
                    )}

                    {/* Products Grid */}
                    <div className="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        {filteredProducts.map((product) => (
                            <Card 
                                key={product.id} 
                                className={`cursor-pointer hover:shadow-md transition-shadow ${
                                    product.stock_quantity === 0 ? 'opacity-50' : ''
                                }`}
                                onClick={() => product.stock_quantity > 0 && addToCart(product)}
                            >
                                <CardContent className="p-4">
                                    <div className="flex justify-between items-start mb-2">
                                        <h3 className="font-semibold text-sm leading-tight">{product.name}</h3>
                                        {product.is_low_stock && (
                                            <Badge variant="destructive" className="text-xs">Low</Badge>
                                        )}
                                    </div>
                                    <div className="text-xs text-gray-600 mb-2">SKU: {product.sku}</div>
                                    <div className="text-lg font-bold text-blue-600 mb-1">
                                        {formatCurrency(product.price)}
                                    </div>
                                    <div className="text-xs text-gray-600">
                                        Stock: {product.stock_quantity}
                                    </div>
                                    {product.category && (
                                        <Badge variant="outline" className="mt-2 text-xs">
                                            {product.category}
                                        </Badge>
                                    )}
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </div>

                {/* Right Panel - Cart */}
                <div className="w-96 bg-white border-l border-gray-200 p-6 flex flex-col">
                    <div className="mb-4">
                        <h2 className="text-xl font-bold text-gray-900">🛒 Shopping Cart</h2>
                        <p className="text-sm text-gray-600">{cart.length} items</p>
                    </div>

                    {/* Customer Selection */}
                    <div className="mb-4">
                        <label className="block text-sm font-medium text-gray-700 mb-2">Customer</label>
                        <Select onValueChange={(value) => {
                            const customer = customers.find(c => c.id.toString() === value);
                            setSelectedCustomer(customer || null);
                        }}>
                            <SelectTrigger>
                                <SelectValue placeholder="Select customer (optional)" />
                            </SelectTrigger>
                            <SelectContent>
                                {customers.map((customer) => (
                                    <SelectItem key={customer.id} value={customer.id.toString()}>
                                        {customer.name} ({customer.loyalty_points} pts)
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    {/* Cart Items */}
                    <div className="flex-1 overflow-auto mb-4">
                        {cart.length === 0 ? (
                            <div className="text-center text-gray-500 py-8">
                                <div className="text-4xl mb-2">🛒</div>
                                <p>Cart is empty</p>
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {cart.map((item) => (
                                    <div key={item.product.id} className="border border-gray-200 rounded-lg p-3">
                                        <div className="flex justify-between items-start mb-2">
                                            <h4 className="font-medium text-sm">{item.product.name}</h4>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => removeFromCart(item.product.id)}
                                                className="text-red-500 hover:text-red-700 p-1 h-auto"
                                            >
                                                ✕
                                            </Button>
                                        </div>
                                        <div className="flex justify-between items-center">
                                            <div className="flex items-center space-x-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => updateQuantity(item.product.id, item.quantity - 1)}
                                                    className="h-8 w-8 p-0"
                                                >
                                                    -
                                                </Button>
                                                <span className="w-8 text-center text-sm">{item.quantity}</span>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => updateQuantity(item.product.id, item.quantity + 1)}
                                                    disabled={item.quantity >= item.product.stock_quantity}
                                                    className="h-8 w-8 p-0"
                                                >
                                                    +
                                                </Button>
                                            </div>
                                            <div className="text-right">
                                                <div className="text-sm font-semibold">
                                                    {formatCurrency(item.total_price)}
                                                </div>
                                                <div className="text-xs text-gray-600">
                                                    {formatCurrency(item.unit_price)} each
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Promotion Code */}
                    <div className="mb-4">
                        <label className="block text-sm font-medium text-gray-700 mb-2">Promotion Code</label>
                        <div className="flex space-x-2">
                            <Input
                                placeholder="Enter code"
                                value={promotionCode}
                                onChange={(e) => setPromotionCode(e.target.value)}
                                className="flex-1"
                            />
                            <Button 
                                variant="outline" 
                                onClick={applyPromotion}
                                disabled={!promotionCode || cart.length === 0}
                            >
                                Apply
                            </Button>
                        </div>
                        {appliedPromotion && (
                            <div className="mt-2 p-2 bg-green-100 rounded flex justify-between items-center">
                                <span className="text-sm text-green-800">
                                    {appliedPromotion.name} applied
                                </span>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={removePromotion}
                                    className="text-green-700 hover:text-green-900 p-1 h-auto"
                                >
                                    ✕
                                </Button>
                            </div>
                        )}
                    </div>

                    {/* Payment Summary */}
                    {cart.length > 0 && (
                        <div className="border-t border-gray-200 pt-4 mb-4">
                            <div className="space-y-2 text-sm">
                                <div className="flex justify-between">
                                    <span>Subtotal:</span>
                                    <span>{formatCurrency(subtotal)}</span>
                                </div>
                                {discountAmount > 0 && (
                                    <div className="flex justify-between text-green-600">
                                        <span>Discount:</span>
                                        <span>-{formatCurrency(discountAmount)}</span>
                                    </div>
                                )}
                                <div className="flex justify-between">
                                    <span>Tax (10%):</span>
                                    <span>{formatCurrency(taxAmount)}</span>
                                </div>
                                <div className="flex justify-between font-bold text-lg border-t border-gray-200 pt-2">
                                    <span>Total:</span>
                                    <span>{formatCurrency(totalAmount)}</span>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Payment Method */}
                    {cart.length > 0 && (
                        <div className="mb-4">
                            <label className="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                            <Select value={paymentMethod} onValueChange={(value: 'cash' | 'card' | 'digital') => setPaymentMethod(value)}>
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="cash">💵 Cash</SelectItem>
                                    <SelectItem value="card">💳 Card</SelectItem>
                                    <SelectItem value="digital">📱 Digital</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    )}

                    {/* Amount Paid */}
                    {cart.length > 0 && (
                        <div className="mb-4">
                            <label className="block text-sm font-medium text-gray-700 mb-2">Amount Paid</label>
                            <Input
                                type="number"
                                placeholder="0"
                                value={amountPaid}
                                onChange={(e) => setAmountPaid(e.target.value)}
                                min={totalAmount}
                            />
                            {parseFloat(amountPaid) >= totalAmount && changeAmount > 0 && (
                                <div className="mt-2 p-2 bg-blue-100 rounded">
                                    <div className="text-sm text-blue-800">
                                        Change: {formatCurrency(changeAmount)}
                                    </div>
                                </div>
                            )}
                        </div>
                    )}

                    {/* Action Buttons */}
                    <div className="space-y-2">
                        <Button
                            onClick={processSale}
                            disabled={cart.length === 0 || parseFloat(amountPaid) < totalAmount}
                            className="w-full"
                            size="lg"
                        >
                            💳 Process Sale
                        </Button>
                        <Button
                            variant="outline"
                            onClick={clearCart}
                            disabled={cart.length === 0}
                            className="w-full"
                        >
                            🗑️ Clear Cart
                        </Button>
                    </div>
                </div>
            </div>
        </AppShell>
    );
}