<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use App\Models\Product;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SliderController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Slider::with('product');

            if ($request->filled('type')) $query->where('type', $request->type);
            if ($request->filled('status')) {
                match ($request->status) {
                    'live'      => $query->scheduled(),
                    'inactive'  => $query->where('is_active', false),
                    'scheduled' => $query->where('is_active', true)
                                         ->where('starts_at', '>', now()),
                    'expired'   => $query->expired(),
                    default     => null,
                };
            }

            return DataTables::of($query)
                ->addColumn('checkbox', fn($s) =>
                    '<input type="checkbox" class="form-check-input bulk-checkbox" value="'.$s->id.'">'
                )
                ->addColumn('slider_col', function ($s) {
                    $badge = $s->badge_text
                        ? '<span class="badge ms-1" style="background:'.($s->badge_color ?: '#7367f0').';font-size:.6rem">'
                          .e($s->badge_text).'</span>'
                        : '';
                    $type = '<span class="badge bg-label-'.match($s->type) {
                        'hero' => 'primary', 'banner' => 'info',
                        'promotional' => 'warning', 'product_spotlight' => 'success',
                        default => 'secondary',
                    }.' mt-1" style="font-size:.6rem">'.ucfirst(str_replace('_', ' ', $s->type)).'</span>';

                    return '
                        <div class="d-flex align-items-center gap-3">
                            <img src="'.$s->image_url.'" class="rounded" width="80" height="45" style="object-fit:cover;">
                            <div>
                                <strong class="d-block">'.e($s->display_title ?: '—').'</strong>
                                '.$badge.$type.'
                            </div>
                        </div>';
                })
                ->addColumn('product_col', fn($s) =>
                    $s->product
                        ? '<span class="badge bg-label-primary">'.e($s->product->title).'</span>'
                        : '<span class="text-muted">—</span>'
                )
                ->addColumn('schedule_col', function ($s) {
                    $parts = [];
                    if ($s->starts_at) $parts[] = '<small class="text-muted">From:</small> '.$s->starts_at->format('M d, Y');
                    if ($s->ends_at) $parts[] = '<small class="text-muted">To:</small> '.$s->ends_at->format('M d, Y');
                    return $parts ? implode('<br>', $parts) : '<span class="text-muted">Always</span>';
                })
                ->addColumn('stats_col', function ($s) {
                    return '<div class="text-center">
                        <span class="d-block fw-semibold">'.number_format($s->views).'</span>
                        <small class="text-muted">views</small>
                    </div>';
                })
                ->addColumn('status_col', function ($s) {
                    $map = [
                        'live'      => ['bg-success', 'Live'],
                        'inactive'  => ['bg-label-secondary', 'Inactive'],
                        'scheduled' => ['bg-label-info', 'Scheduled'],
                        'expired'   => ['bg-label-danger', 'Expired'],
                    ];
                    [$cls, $lbl] = $map[$s->status_label] ?? ['bg-secondary', $s->status_label];
                    return '<span class="badge '.$cls.'">'.$lbl.'</span>';
                })
                ->addColumn('actions', function ($s) {
                    $editUrl   = route('sliders.edit', $s);
                    $deleteUrl = route('sliders.destroy', $s);
                    $toggleTip = $s->is_active ? 'Deactivate' : 'Activate';
                    $toggleIcon = $s->is_active ? 'tabler-eye-off' : 'tabler-eye';
                    $toggleCls = $s->is_active ? 'btn-label-warning' : 'btn-label-success';

                    return '
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <button type="button" class="btn btn-icon btn-sm '.$toggleCls.' toggle-btn"
                                    data-id="'.$s->id.'" data-active="'.($s->is_active ? 1 : 0).'" title="'.$toggleTip.'">
                                <i class="ti '.$toggleIcon.' ti-xs"></i>
                            </button>
                            <a href="'.$editUrl.'" class="btn btn-icon btn-sm btn-label-primary" title="Edit">
                                <i class="ti tabler-pencil ti-xs"></i>
                            </a>
                            <button type="button" class="btn btn-icon btn-sm btn-label-danger delete-btn"
                                    data-url="'.$deleteUrl.'" title="Delete">
                                <i class="ti tabler-trash ti-xs"></i>
                            </button>
                        </div>';
                })
                ->rawColumns(['checkbox', 'slider_col', 'product_col', 'schedule_col', 'stats_col', 'status_col', 'actions'])
                ->make(true);
        }

        $stats = [
            'total'     => Slider::count(),
            'live'      => Slider::scheduled()->count(),
            'scheduled' => Slider::where('is_active', true)->where('starts_at', '>', now())->count(),
            'expired'   => Slider::expired()->count(),
            'inactive'  => Slider::where('is_active', false)->count(),
            'clicks'    => Slider::sum('clicks'),
            'views'     => Slider::sum('views'),
        ];

        return view('content.sliders.index', compact('stats'));
    }

    public function create()
    {
        $products = Product::where('status', 'active')->orderBy('title')->get();
        $maxPos   = Slider::max('position') ?? 0;
        return view('content.sliders.create', compact('products', 'maxPos'));
    }

    public function store(Request $request)
    {

        $data = $request->validate([
            'type'          => 'required|in:hero,banner,promotional,product_spotlight',
            'title'         => 'nullable|string|max:255',
            'subtitle'      => 'nullable|string|max:255',
            'badge_text'    => 'nullable|string|max:50',
            'badge_color'   => 'nullable|string|max:20',
            'product_id'    => 'nullable|exists:products,id',
            'image'         => 'required|image|max:5120',
            'overlay_color' => 'nullable|string|max:50',
            'text_color'    => 'required|in:light,dark',
            'text_position' => 'required|in:left,center,right',
            'button_text'   => 'nullable|string|max:50',
            'button_url'    => 'nullable|url|max:500',
            'position'      => 'nullable|integer|min:0',
            'starts_at'     => 'nullable|date',
            'ends_at'       => 'nullable|date|after_or_equal:starts_at',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $name = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $dest = public_path('uploads/sliders');
            if (!is_dir($dest)) mkdir($dest, 0755, true);
            $file->move($dest, $name);
            $data['image'] = 'uploads/sliders/'.$name;
        }

        Slider::create($data);

        return redirect()->route('sliders.index')->with('success', 'Slider created successfully.');
    }

    public function edit(Slider $slider)
    {
        $products = Product::where('status', 'active')->orderBy('title')->get();
        return view('content.sliders.edit', compact('slider', 'products'));
    }

    public function update(Request $request, Slider $slider)
    {

        $data = $request->validate([
            'type'          => 'required|in:hero,banner,promotional,product_spotlight',
            'title'         => 'nullable|string|max:255',
            'subtitle'      => 'nullable|string|max:255',
            'badge_text'    => 'nullable|string|max:50',
            'badge_color'   => 'nullable|string|max:20',
            'product_id'    => 'nullable|exists:products,id',
            'image'         => 'nullable|image|max:5120',
            'overlay_color' => 'nullable|string|max:50',
            'text_color'    => 'required|in:light,dark',
            'text_position' => 'required|in:left,center,right',
            'button_text'   => 'nullable|string|max:50',
            'button_url'    => 'nullable|url|max:500',
            'position'      => 'nullable|integer|min:0',
            'starts_at'     => 'nullable|date',
            'ends_at'       => 'nullable|date|after_or_equal:starts_at',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if (!$request->filled('product_id')) {
            $data['product_id'] = null;
        }

        if ($request->hasFile('image')) {
            if ($slider->image && file_exists(public_path($slider->image))) {
                @unlink(public_path($slider->image));
            }
            $file = $request->file('image');
            $name = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $dest = public_path('uploads/sliders');
            if (!is_dir($dest)) mkdir($dest, 0755, true);
            $file->move($dest, $name);
            $data['image'] = 'uploads/sliders/'.$name;
        }

        $slider->update($data);

        return redirect()->route('sliders.index')->with('success', 'Slider updated successfully.');
    }

    public function destroy(Slider $slider)
    {

        if ($slider->image && file_exists(public_path($slider->image))) {
            @unlink(public_path($slider->image));
        }
        $slider->delete();

        return response()->json(['message' => 'Slider deleted successfully.']);
    }

    /* ── AJAX Actions ───────────────────────────────── */

    public function toggleStatus(Request $request, Slider $slider)
    {
        $slider->update(['is_active' => !$slider->is_active]);
        return response()->json([
            'message' => 'Slider '.($slider->is_active ? 'activated' : 'deactivated').'.',
            'is_active' => $slider->is_active,
        ]);
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer|exists:sliders,id']);
        foreach ($request->order as $pos => $id) {
            Slider::where('id', $id)->update(['position' => $pos]);
        }
        return response()->json(['message' => 'Order updated.']);
    }

    public function duplicate(Slider $slider)
    {
        $copy = $slider->replicate(['clicks', 'views']);
        $copy->title    = ($slider->title ?? 'Slider').' (Copy)';
        $copy->position = Slider::max('position') + 1;
        $copy->is_active = false;
        $copy->save();

        return response()->json(['message' => 'Slider duplicated.', 'id' => $copy->id]);
    }

    public function bulkDelete(Request $request)
    {

        $sliders = Slider::whereIn('id', $request->ids)->get();
        foreach ($sliders as $s) {
            if ($s->image && file_exists(public_path($s->image))) @unlink(public_path($s->image));
            $s->delete();
        }
        return response()->json(['message' => count($request->ids).' slider(s) deleted.']);
    }
}
