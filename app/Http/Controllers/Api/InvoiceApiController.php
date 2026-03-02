<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Orders
 *
 * Invoice download endpoint for authenticated users.
 */
class InvoiceApiController extends Controller
{
    /**
     * Download invoice PDF
     *
     * Download the invoice for an order as a PDF file.
     * The order must belong to the authenticated user.
     *
     * @authenticated
     *
     * @urlParam order integer required Order ID. Example: 42
     *
     * @response 200 {binary PDF file}
     * @response 403 {"status":false,"message":"Unauthorized"}
     * @response 404 {"status":false,"message":"Invoice not available"}
     */
    public function download(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return $this->error('Unauthorized.', Response::HTTP_FORBIDDEN);
        }

        $order->load('invoice');

        if (!$order->invoice) {
            return $this->error('Invoice not available yet.', Response::HTTP_NOT_FOUND);
        }

        $html = InvoiceService::renderHtml($order->invoice);
        $pdf = Pdf::loadHTML($html)->setPaper('a4');

        return $pdf->download('Invoice-' . $order->invoice->invoice_number . '.pdf');
    }
}
