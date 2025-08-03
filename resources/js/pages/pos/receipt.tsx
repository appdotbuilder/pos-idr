import React from 'react';
import { AppShell } from '@/components/app-shell';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { router } from '@inertiajs/react';
import { formatCurrency } from '@/lib/utils';

interface Sale {
    id: number;
    transaction_number: string;
    customer: {
        id: number;
        name: string;
        email: string;
        phone: string;
    } | null;
    user: {
        id: number;
        name: string;
    };
    subtotal: number;
    discount_amount: number;
    tax_amount: number;
    total_amount: number;
    payment_method: string;
    amount_paid: number;
    change_amount: number;
    status: string;
    notes: string | null;
    created_at: string;
    sale_items: Array<{
        id: number;
        quantity: number;
        unit_price: number;
        discount_amount: number;
        total_price: number;
        product: {
            id: number;
            name: string;
            sku: string;
        };
    }>;
}

interface Props {
    sale: Sale;
    [key: string]: unknown;
}

export default function Receipt({ sale }: Props) {
    const printReceipt = () => {
        window.print();
    };

    const newSale = () => {
        router.get('/pos');
    };

    const paymentMethodIcons = {
        cash: '💵',
        card: '💳',
        digital: '📱'
    };

    return (
        <AppShell>
            <div className="max-w-4xl mx-auto p-6">
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-2xl font-bold text-gray-900">🧾 Sale Receipt</h1>
                    <div className="flex space-x-3">
                        <Button onClick={printReceipt} variant="outline">
                            🖨️ Print Receipt
                        </Button>
                        <Button onClick={newSale}>
                            ➕ New Sale
                        </Button>
                    </div>
                </div>

                {/* Receipt Card */}
                <Card className="max-w-2xl mx-auto">
                    <CardHeader className="text-center border-b">
                        <CardTitle className="text-2xl">SmartPOS</CardTitle>
                        <p className="text-gray-600">Complete Point of Sale Solution</p>
                        <div className="text-sm text-gray-500 mt-2">
                            Transaction: {sale.transaction_number}
                        </div>
                    </CardHeader>

                    <CardContent className="p-6 space-y-6">
                        {/* Transaction Details */}
                        <div className="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <div className="font-medium text-gray-700">Date & Time</div>
                                <div>{new Date(sale.created_at).toLocaleString('id-ID')}</div>
                            </div>
                            <div>
                                <div className="font-medium text-gray-700">Cashier</div>
                                <div>{sale.user.name}</div>
                            </div>
                            {sale.customer && (
                                <>
                                    <div>
                                        <div className="font-medium text-gray-700">Customer</div>
                                        <div>{sale.customer.name}</div>
                                    </div>
                                    <div>
                                        <div className="font-medium text-gray-700">Contact</div>
                                        <div>{sale.customer.phone || sale.customer.email}</div>
                                    </div>
                                </>
                            )}
                        </div>

                        {/* Items */}
                        <div>
                            <h3 className="font-medium text-gray-700 mb-3">Items Purchased</h3>
                            <div className="space-y-3">
                                {sale.sale_items.map((item, index) => (
                                    <div key={index} className="flex justify-between items-start">
                                        <div className="flex-1">
                                            <div className="font-medium">{item.product.name}</div>
                                            <div className="text-sm text-gray-600">
                                                SKU: {item.product.sku}
                                            </div>
                                            <div className="text-sm text-gray-600">
                                                {item.quantity} × {formatCurrency(item.unit_price)}
                                                {item.discount_amount > 0 && (
                                                    <span className="text-green-600">
                                                        {' '}(Disc: {formatCurrency(item.discount_amount)})
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                        <div className="font-medium">
                                            {formatCurrency(item.total_price)}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Payment Summary */}
                        <div className="border-t pt-4">
                            <div className="space-y-2">
                                <div className="flex justify-between">
                                    <span>Subtotal:</span>
                                    <span>{formatCurrency(sale.subtotal)}</span>
                                </div>
                                {sale.discount_amount > 0 && (
                                    <div className="flex justify-between text-green-600">
                                        <span>Discount:</span>
                                        <span>-{formatCurrency(sale.discount_amount)}</span>
                                    </div>
                                )}
                                <div className="flex justify-between">
                                    <span>Tax (10%):</span>
                                    <span>{formatCurrency(sale.tax_amount)}</span>
                                </div>
                                <div className="flex justify-between font-bold text-lg border-t pt-2">
                                    <span>Total:</span>
                                    <span>{formatCurrency(sale.total_amount)}</span>
                                </div>
                            </div>
                        </div>

                        {/* Payment Details */}
                        <div className="border-t pt-4">
                            <div className="space-y-2">
                                <div className="flex justify-between">
                                    <span>Payment Method:</span>
                                    <span className="flex items-center space-x-1">
                                        <span>{paymentMethodIcons[sale.payment_method as keyof typeof paymentMethodIcons]}</span>
                                        <span className="capitalize">{sale.payment_method}</span>
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Amount Paid:</span>
                                    <span>{formatCurrency(sale.amount_paid)}</span>
                                </div>
                                {sale.change_amount > 0 && (
                                    <div className="flex justify-between font-medium text-blue-600">
                                        <span>Change:</span>
                                        <span>{formatCurrency(sale.change_amount)}</span>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Notes */}
                        {sale.notes && (
                            <div className="border-t pt-4">
                                <div className="font-medium text-gray-700 mb-1">Notes:</div>
                                <div className="text-sm text-gray-600">{sale.notes}</div>
                            </div>
                        )}

                        {/* Footer */}
                        <div className="border-t pt-4 text-center text-sm text-gray-500">
                            <p>Thank you for your purchase!</p>
                            <p>Visit us again soon</p>
                            <p className="mt-2">SmartPOS - Your Trusted Retail Partner</p>
                        </div>
                    </CardContent>
                </Card>

                {/* Action Buttons (visible only on screen) */}
                <div className="flex justify-center space-x-4 mt-6 print:hidden">
                    <Button onClick={printReceipt} variant="outline" size="lg">
                        🖨️ Print Receipt
                    </Button>
                    <Button onClick={newSale} size="lg">
                        ➕ Process New Sale
                    </Button>
                </div>
            </div>

            {/* Print Styles */}
            <style>{`
                @media print {
                    body * {
                        visibility: hidden;
                    }
                    
                    .print-area,
                    .print-area * {
                        visibility: visible;
                    }
                    
                    .print-area {
                        position: absolute;
                        left: 0;
                        top: 0;
                        width: 100%;
                    }
                    
                    .print\\:hidden {
                        display: none !important;
                    }
                }
            `}</style>
        </AppShell>
    );
}