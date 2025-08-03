import React from 'react';
import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

interface Props {
    canLogin?: boolean;
    canRegister?: boolean;
    [key: string]: unknown;
}

export default function Welcome({ canLogin, canRegister }: Props) {
    return (
        <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
            {/* Header */}
            <header className="relative">
                <div className="container mx-auto px-4 py-6">
                    <div className="flex justify-between items-center">
                        <div className="flex items-center space-x-3">
                            <div className="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                <span className="text-white font-bold text-lg">💳</span>
                            </div>
                            <h1 className="text-2xl font-bold text-gray-900">SmartPOS</h1>
                        </div>
                        
                        {(canLogin || canRegister) && (
                            <div className="flex space-x-4">
                                {canLogin && (
                                    <Link href="/login">
                                        <Button variant="outline" size="sm">
                                            Log in
                                        </Button>
                                    </Link>
                                )}
                                {canRegister && (
                                    <Link href="/register">
                                        <Button size="sm">
                                            Sign up
                                        </Button>
                                    </Link>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </header>

            {/* Hero Section */}
            <section className="container mx-auto px-4 py-16">
                <div className="max-w-4xl mx-auto text-center">
                    <h2 className="text-5xl font-bold text-gray-900 mb-6">
                        🏪 Complete Point of Sale Solution
                    </h2>
                    <p className="text-xl text-gray-600 mb-8 leading-relaxed">
                        Streamline your retail operations with our comprehensive POS system. 
                        Manage products, track inventory, process sales, and analyze your business performance - all in one place.
                    </p>
                    
                    <div className="flex justify-center space-x-4 mb-12">
                        {canLogin && (
                            <Link href="/login">
                                <Button size="lg" className="px-8 py-3">
                                    🚀 Get Started
                                </Button>
                            </Link>
                        )}
                        <Button variant="outline" size="lg" className="px-8 py-3">
                            📋 View Demo
                        </Button>
                    </div>

                    {/* Currency Notice */}
                    <div className="inline-flex items-center px-4 py-2 bg-green-100 rounded-full text-green-800 text-sm font-medium">
                        💰 Optimized for Indonesian Rupiah (IDR)
                    </div>
                </div>
            </section>

            {/* Features Section */}
            <section className="container mx-auto px-4 py-16">
                <div className="max-w-6xl mx-auto">
                    <h3 className="text-3xl font-bold text-center text-gray-900 mb-12">
                        ✨ Everything You Need to Run Your Business
                    </h3>
                    
                    <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                        {/* Product Management */}
                        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                            <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                                <span className="text-2xl">📦</span>
                            </div>
                            <h4 className="text-xl font-semibold text-gray-900 mb-3">Product Management</h4>
                            <p className="text-gray-600 leading-relaxed">
                                Add, manage, and track all your products with SKU codes, categories, pricing, and stock levels.
                            </p>
                        </div>

                        {/* Inventory Tracking */}
                        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                            <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                                <span className="text-2xl">📊</span>
                            </div>
                            <h4 className="text-xl font-semibold text-gray-900 mb-3">Smart Inventory</h4>
                            <p className="text-gray-600 leading-relaxed">
                                Real-time stock tracking with low stock alerts and automated inventory movement logging.
                            </p>
                        </div>

                        {/* Sales Processing */}
                        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                            <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                                <span className="text-2xl">💳</span>
                            </div>
                            <h4 className="text-xl font-semibold text-gray-900 mb-3">Multi-Payment Processing</h4>
                            <p className="text-gray-600 leading-relaxed">
                                Accept cash, card, and digital payments with automatic change calculation and receipt printing.
                            </p>
                        </div>

                        {/* Customer Management */}
                        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                            <div className="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                                <span className="text-2xl">👥</span>
                            </div>
                            <h4 className="text-xl font-semibold text-gray-900 mb-3">Customer & Loyalty</h4>
                            <p className="text-gray-600 leading-relaxed">
                                Manage customer profiles and reward loyal customers with points-based loyalty program.
                            </p>
                        </div>

                        {/* Promotions */}
                        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                            <div className="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                                <span className="text-2xl">🎯</span>
                            </div>
                            <h4 className="text-xl font-semibold text-gray-900 mb-3">Discounts & Promotions</h4>
                            <p className="text-gray-600 leading-relaxed">
                                Create and manage discount codes, percentage-based or fixed-amount promotions with usage limits.
                            </p>
                        </div>

                        {/* Reports & Analytics */}
                        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                            <div className="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                                <span className="text-2xl">📈</span>
                            </div>
                            <h4 className="text-xl font-semibold text-gray-900 mb-3">Advanced Reporting</h4>
                            <p className="text-gray-600 leading-relaxed">
                                Comprehensive sales reports, inventory analysis, and customer insights to grow your business.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {/* User Roles Section */}
            <section className="bg-gray-50 py-16">
                <div className="container mx-auto px-4">
                    <div className="max-w-4xl mx-auto text-center">
                        <h3 className="text-3xl font-bold text-gray-900 mb-8">
                            🔐 Role-Based Access Control
                        </h3>
                        <div className="grid md:grid-cols-3 gap-6">
                            <div className="bg-white rounded-lg p-6 shadow-sm">
                                <div className="text-3xl mb-3">👑</div>
                                <h4 className="font-semibold text-lg text-gray-900 mb-2">Admin</h4>
                                <p className="text-gray-600 text-sm">Full system access, user management, and business analytics.</p>
                            </div>
                            <div className="bg-white rounded-lg p-6 shadow-sm">
                                <div className="text-3xl mb-3">👨‍💼</div>
                                <h4 className="font-semibold text-lg text-gray-900 mb-2">Manager</h4>
                                <p className="text-gray-600 text-sm">Product management, inventory control, and sales reporting.</p>
                            </div>
                            <div className="bg-white rounded-lg p-6 shadow-sm">
                                <div className="text-3xl mb-3">💼</div>
                                <h4 className="font-semibold text-lg text-gray-900 mb-2">Cashier</h4>
                                <p className="text-gray-600 text-sm">Sales processing, customer management, and basic reporting.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* CTA Section */}
            <section className="container mx-auto px-4 py-16">
                <div className="max-w-3xl mx-auto text-center">
                    <h3 className="text-3xl font-bold text-gray-900 mb-6">
                        Ready to Transform Your Business?
                    </h3>
                    <p className="text-lg text-gray-600 mb-8">
                        Join thousands of retailers who trust SmartPOS to manage their daily operations efficiently.
                    </p>
                    
                    {canLogin && (
                        <div className="flex justify-center space-x-4">
                            <Link href="/login">
                                <Button size="lg" className="px-8 py-3">
                                    🎯 Start Selling Now
                                </Button>
                            </Link>
                            {canRegister && (
                                <Link href="/register">
                                    <Button variant="outline" size="lg" className="px-8 py-3">
                                        📝 Create Account
                                    </Button>
                                </Link>
                            )}
                        </div>
                    )}
                </div>
            </section>

            {/* Footer */}
            <footer className="bg-gray-900 text-white py-12">
                <div className="container mx-auto px-4 text-center">
                    <div className="flex justify-center items-center space-x-3 mb-4">
                        <div className="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                            <span className="text-white font-bold">💳</span>
                        </div>
                        <h4 className="text-xl font-bold">SmartPOS</h4>
                    </div>
                    <p className="text-gray-400 mb-4">
                        Complete Point of Sale Solution for Modern Retailers
                    </p>
                    <p className="text-gray-500 text-sm">
                        Built with Laravel & React • Optimized for Indonesian Market
                    </p>
                </div>
            </footer>
        </div>
    );
}