<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    /**
     * Display the billing dashboard.
     */
    public function index()
    {
        // Total invoices
        $totalInvoices = Invoice::count();

        // Paid invoices
        $paidInvoices = Invoice::where('status', InvoiceStatus::PAID)->count();

        // Unpaid invoices (unpaid + partial_paid + overdue)
        $unpaidInvoices = Invoice::unpaid()->count();

        // Monthly revenue (current month)
        $monthlyRevenue = Payment::whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month)
            ->sum('amount');

        // Total payments
        $totalPayments = Payment::sum('amount');

        // Recent invoices (last 10)
        $recentInvoices = Invoice::with(['patient', 'visit', 'createdBy'])
            ->latest('issued_at')
            ->limit(10)
            ->get();

        // Recent payments (last 10)
        $recentPayments = Payment::with(['patient', 'invoice', 'receivedBy'])
            ->latest('payment_date')
            ->limit(10)
            ->get();

        return view('admin.billing.index', compact(
            'totalInvoices',
            'paidInvoices',
            'unpaidInvoices',
            'monthlyRevenue',
            'totalPayments',
            'recentInvoices',
            'recentPayments'
        ));
    }

    /**
     * Display the invoices page.
     */
    public function invoices(Request $request)
    {
        return redirect()->route('admin.billing.invoices.index', $request->query());
    }

    /**
     * Display the payments page.
     */
    public function payments(Request $request)
    {
        return redirect()->route('admin.billing.payments.index', $request->query());
    }
}
