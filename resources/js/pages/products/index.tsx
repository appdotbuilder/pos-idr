import React from 'react';
import { AppShell } from '@/components/app-shell';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Link, router } from '@inertiajs/react';
import { formatCurrency } from '@/lib/utils';

interface Product {
    id: number;
    name: string;
    sku: string;
    description: string | null;
    price: number;
    stock_quantity: number;
    low_stock_threshold: number;
    category: string | null;
    is_active: boolean;
    is_low_stock: boolean;
    created_at: string;
}

interface Props {
    products: {
        data: Product[];
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        meta: {
            last_page: number;
        };
    };
    categories: string[];
    filters: {
        search?: string;
        category?: string;
        low_stock?: boolean;
    };
    [key: string]: unknown;
}

export default function ProductsIndex({ products, categories, filters }: Props) {
    const handleSearch = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        const formData = new FormData(e.currentTarget);
        const search = formData.get('search') as string;
        
        router.get('/products', {
            ...filters,
            search: search || undefined,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const filterByCategory = (category: string | null) => {
        router.get('/products', {
            ...filters,
            category: category || undefined,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const toggleLowStockFilter = () => {
        router.get('/products', {
            ...filters,
            low_stock: !filters.low_stock ? '1' : undefined,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <AppShell>
            <div className="p-6">
                <div className="flex justify-between items-center mb-6">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">📦 Product Management</h1>
                        <p className="text-gray-600">Manage your inventory and product catalog</p>
                    </div>
                    <Link href="/products/create">
                        <Button>➕ Add Product</Button>
                    </Link>
                </div>

                {/* Filters */}
                <div className="bg-white rounded-lg border border-gray-200 p-4 mb-6">
                    <div className="flex flex-wrap items-center gap-4">
                        <form onSubmit={handleSearch} className="flex-1 min-w-64">
                            <Input
                                name="search"
                                placeholder="🔍 Search products by name or SKU..."
                                defaultValue={filters.search}
                                className="w-full"
                            />
                        </form>

                        <div className="flex items-center space-x-2">
                            <Button
                                variant={filters.category ? "default" : "outline"}
                                size="sm"
                                onClick={() => filterByCategory(null)}
                            >
                                All Categories
                            </Button>
                            {categories.map((category) => (
                                <Button
                                    key={category}
                                    variant={filters.category === category ? "default" : "outline"}
                                    size="sm"
                                    onClick={() => filterByCategory(category)}
                                >
                                    {category}
                                </Button>
                            ))}
                        </div>

                        <Button
                            variant={filters.low_stock ? "destructive" : "outline"}
                            size="sm"
                            onClick={toggleLowStockFilter}
                        >
                            ⚠️ Low Stock Only
                        </Button>
                    </div>
                </div>

                {/* Products Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    {products.data.map((product) => (
                        <Card key={product.id} className="hover:shadow-md transition-shadow">
                            <CardHeader>
                                <div className="flex justify-between items-start">
                                    <CardTitle className="text-lg leading-tight">
                                        {product.name}
                                    </CardTitle>
                                    <div className="flex flex-col items-end space-y-1">
                                        {!product.is_active && (
                                            <Badge variant="secondary">Inactive</Badge>
                                        )}
                                        {product.is_low_stock && product.is_active && (
                                            <Badge variant="destructive">Low Stock</Badge>
                                        )}
                                        {product.stock_quantity === 0 && product.is_active && (
                                            <Badge variant="destructive">Out of Stock</Badge>
                                        )}
                                    </div>
                                </div>
                                <div className="text-sm text-gray-600">SKU: {product.sku}</div>
                            </CardHeader>

                            <CardContent>
                                <div className="space-y-3">
                                    {product.description && (
                                        <p className="text-sm text-gray-600 line-clamp-2">
                                            {product.description}
                                        </p>
                                    )}

                                    <div className="flex justify-between items-center">
                                        <div className="text-2xl font-bold text-blue-600">
                                            {formatCurrency(product.price)}
                                        </div>
                                        {product.category && (
                                            <Badge variant="outline">{product.category}</Badge>
                                        )}
                                    </div>

                                    <div className="flex justify-between items-center text-sm">
                                        <span className="text-gray-600">Stock:</span>
                                        <span className={`font-medium ${
                                            product.stock_quantity <= product.low_stock_threshold
                                                ? 'text-red-600'
                                                : 'text-green-600'
                                        }`}>
                                            {product.stock_quantity} units
                                        </span>
                                    </div>

                                    <div className="flex justify-between items-center text-sm">
                                        <span className="text-gray-600">Low Stock Alert:</span>
                                        <span className="font-medium">{product.low_stock_threshold} units</span>
                                    </div>

                                    <div className="flex space-x-2 pt-2">
                                        <Link href={`/products/${product.id}`} className="flex-1">
                                            <Button variant="outline" size="sm" className="w-full">
                                                👁️ View
                                            </Button>
                                        </Link>
                                        <Link href={`/products/${product.id}/edit`} className="flex-1">
                                            <Button variant="outline" size="sm" className="w-full">
                                                ✏️ Edit
                                            </Button>
                                        </Link>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* Empty State */}
                {products.data.length === 0 && (
                    <div className="text-center py-12">
                        <div className="text-6xl mb-4">📦</div>
                        <h3 className="text-xl font-semibold text-gray-900 mb-2">No products found</h3>
                        <p className="text-gray-600 mb-4">
                            {filters.search || filters.category || filters.low_stock
                                ? 'Try adjusting your filters or search terms.'
                                : 'Get started by adding your first product.'}
                        </p>
                        <Link href="/products/create">
                            <Button>➕ Add Your First Product</Button>
                        </Link>
                    </div>
                )}

                {/* Pagination */}
                {products.meta.last_page > 1 && (
                    <div className="flex justify-center mt-8">
                        <div className="flex space-x-2">
                            {products.links.map((link, index) => (
                                <Button
                                    key={index}
                                    variant={link.active ? "default" : "outline"}
                                    size="sm"
                                    disabled={!link.url}
                                    onClick={() => link.url && router.get(link.url)}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AppShell>
    );
}