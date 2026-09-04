<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->hasRole('super_admin');
    }

    public function create(User $user): bool
    {
        // Invoices are created by the billing:generate-invoices command, not manually
        return false;
    }

    public function update(User $user, Invoice $invoice): bool
    {
        // Once created, invoices are read-only to ensure audit trail integrity
        return false;
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        // Invoices should never be deleted; soft-delete if needed for compliance
        return false;
    }
}
