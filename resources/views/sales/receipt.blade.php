<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $sale->invoice_number }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ── Screen styles ── */
        body {
            background: #f3f4f6;
        }

        .receipt-wrap {
            max-width: 420px;
            margin: 2rem auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            font-family: 'Courier New', monospace;
        }

        /* ── Print styles — thermal 80mm ── */
        @media print {
            body {
                background: #fff;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            .receipt-wrap {
                max-width: 80mm;
                width: 80mm;
                margin: 0;
                padding: 4mm;
                box-shadow: none;
                border-radius: 0;
                font-size: 11px;
            }

            .shop-name {
                font-size: 14px;
            }

            .invoice-num {
                font-size: 11px;
            }

            .total-row {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>

    {{-- Action buttons — hidden on print --}}
    <div class="no-print flex items-center justify-between max-w-xl mx-auto px-4 pt-6 pb-2">
        <a href="{{ route('pos') }}"
            class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7" />
            </svg>
            Back to POS
        </a>
        <button onclick="window.print()"
            class="flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print receipt
        </button>
    </div>

    {{-- Receipt --}}
    <div class="receipt-wrap">

        {{-- Shop header --}}
        <div class="text-center mb-4">
            <div class="shop-name font-bold text-lg tracking-wide">PRASAD TECH</div>
            <div class="text-xs text-gray-500 mt-0.5">Communication & Book Shop</div>
            <div class="text-xs text-gray-400 mt-0.5">Tel: 077-0202361</div>
        </div>

        <div class="border-t border-dashed border-gray-300 my-3"></div>

        {{-- Invoice details --}}
        <div class="text-xs space-y-0.5 mb-3">
            <div class="flex justify-between">
                <span class="text-gray-500">Invoice</span>
                <span class="invoice-num font-semibold">{{ $sale->invoice_number }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Date</span>
                <span>{{ $sale->sold_at->format('d M Y  h:i A') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Cashier</span>
                <span>{{ $sale->user->name }}</span>
            </div>
            @if($sale->customer)
                <div class="flex justify-between">
                    <span class="text-gray-500">Customer</span>
                    <span>{{ $sale->customer->name }}</span>
                </div>
            @endif
        </div>

        <div class="border-t border-dashed border-gray-300 my-3"></div>

        {{-- Items --}}
        <table class="w-full text-xs mb-3">
            <thead>
                <tr class="text-gray-400">
                    <th class="text-left pb-1.5 font-medium">Item</th>
                    <th class="text-center pb-1.5 font-medium w-8">Qty</th>
                    <th class="text-right pb-1.5 font-medium">Price</th>
                    <th class="text-right pb-1.5 font-medium">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                    <tr class="border-t border-gray-100">
                        <td class="py-1.5 pr-2">
                            <div class="font-medium text-gray-800 leading-tight">{{ $item->product_name }}</div>
                            <div class="text-gray-400 text-xs">{{ $item->product_sku }}</div>
                        </td>
                        <td class="py-1.5 text-center text-gray-600">{{ $item->quantity }}</td>
                        <td class="py-1.5 text-right text-gray-600">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-1.5 text-right font-medium text-gray-800">{{ number_format($item->line_total, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="border-t border-dashed border-gray-300 my-3"></div>

        {{-- Totals --}}
        <div class="text-xs space-y-1 mb-3">
            <div class="flex justify-between text-gray-500">
                <span>Subtotal</span>
                <span>Rs. {{ number_format($sale->subtotal, 2) }}</span>
            </div>

            @if($sale->discount_amount > 0)
                <div class="flex justify-between text-green-600">
                    <span>Discount</span>
                    <span>− Rs. {{ number_format($sale->discount_amount, 2) }}</span>
                </div>
            @endif

            <div class="total-row flex justify-between font-bold text-gray-900 text-sm pt-1 border-t border-gray-200">
                <span>TOTAL</span>
                <span>Rs. {{ number_format($sale->total_amount, 2) }}</span>
            </div>
        </div>

        <div class="border-t border-dashed border-gray-300 my-3"></div>

        {{-- Payment --}}
        <div class="text-xs space-y-1 mb-3">
            @foreach($sale->payments as $payment)
                <div class="flex justify-between text-gray-500">
                    <span>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</span>
                    <span>Rs. {{ number_format($payment->amount, 2) }}</span>
                </div>
            @endforeach

            @if($sale->change_amount > 0)
                <div class="flex justify-between text-gray-500">
                    <span>Change</span>
                    <span>Rs. {{ number_format($sale->change_amount, 2) }}</span>
                </div>
            @endif
        </div>

        @if($sale->note)
            <div class="border-t border-dashed border-gray-300 my-3"></div>
            <div class="text-xs text-gray-400 italic">Note: {{ $sale->note }}</div>
        @endif

        <div class="border-t border-dashed border-gray-300 my-3"></div>

        {{-- Footer --}}
        <div class="text-center text-xs text-gray-400 mt-2 space-y-0.5">
            <div>Thank you for shopping with us!</div>
            <div>Goods once sold are not returnable.</div>
        </div>

    </div>

    {{-- Auto-print if coming from a completed sale --}}
    @if(session('print_receipt'))
        <script>
            // Auto-print when opened as popup, then close
            window.onload = () => {
                window.print();
            };
        </script>
    @endif

</body>

</html>