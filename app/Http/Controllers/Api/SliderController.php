<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;

/**
 * @group Sliders
 *
 * APIs for homepage sliders / banners.
 * Used by frontend to render hero sliders.
 */
class SliderController extends Controller
{
    /**
     * List active sliders
     *
     * Retrieve all active sliders ordered by position.
     *
     * @queryParam position int Optional. Filter by position. Example: 1
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Sliders fetched successfully",
     *   "data": []
     * }
     */
    public function index(Request $request)
    {
        $sliders = Slider::with('product')
            ->where('is_active', true)
            ->when($request->position, fn ($q) => $q->where('position', $request->position))
            ->orderBy('position')
            ->get();

        return $this->successResponse(
            $sliders->map(fn ($slider) => $this->transform($slider)),
            'Sliders fetched successfully'
        );
    }

    /**
     * Get slider details
     *
     * Retrieve a single slider by ID.
     *
     * @urlParam id int required Slider ID. Example: 3
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Slider details fetched",
     *   "data": {
     *     "title": "Windows 11 Pro"
     *   }
     * }
     */
    public function show($id)
    {
        $slider = Slider::with('product')
            ->where('is_active', true)
            ->find($id);

        if (!$slider) {
            return $this->errorResponse('Slider not found', 404);
        }

        return $this->successResponse(
            $this->transform($slider),
            'Slider details fetched'
        );
    }

    /* --------------------------------
     | Data Transformer
     |-------------------------------- */

    protected function transform(Slider $slider): array
    {
        return [
            'id'        => $slider->id,
            'title'     => $slider->display_title,
            'subtitle'  => $slider->display_subtitle,
            'image'     => $slider->image,
            'button'    => [
                'text' => $slider->button_text,
                'url'  => $slider->display_url,
            ],
            'position'  => $slider->position,
            'product'   => $slider->product ? [
                'id'    => $slider->product->id,
                'title' => $slider->product->title,
                'slug'  => $slider->product->slug,
            ] : null,
        ];
    }

    /* --------------------------------
     | API Response Helpers
     |-------------------------------- */

    protected function successResponse($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    protected function errorResponse($message, $code = 400)
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
        ], $code);
    }
}
