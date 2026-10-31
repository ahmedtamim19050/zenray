<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quotations, invoices, their line items and invoice payments.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Columns shared by quotes and invoices: customer snapshot and totals.
        $document = function (Blueprint $table) {
            $table->id();
            $table->string('number', 40)->unique();
            $table->foreignId('user_id')->nullable()->comment('Customer account')->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('draft')->index();
            $table->string('customer_name', 160);
            $table->string('customer_organization', 160)->nullable();
            $table->string('customer_email', 180)->nullable();
            $table->string('customer_phone', 40)->nullable();
            $table->text('customer_address')->nullable();
            $table->date('issue_date');
            $table->string('currency', 3);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
        };

        Schema::create('quotes', function (Blueprint $table) use ($document) {
            $document($table);
            $table->foreignId('enquiry_id')->nullable()->constrained()->nullOnDelete();
            $table->date('valid_until')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) use ($document) {
            $document($table);
            $table->foreignId('quote_id')->nullable()->constrained()->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->timestamps();
        });

        Schema::create('line_items', function (Blueprint $table) {
            $table->id();
            $table->morphs('document');
            $table->foreignId('inventory_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description', 500);
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 20)->nullable();
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('paid_on');
            $table->string('method', 30);
            $table->string('reference', 120)->nullable();
            $table->string('note', 255)->nullable();
            $table->timestamps();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', fn (Blueprint $table) => $table->dropForeign(['invoice_id']));
        Schema::dropIfExists('payments');
        Schema::dropIfExists('line_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('quotes');
    }
};
