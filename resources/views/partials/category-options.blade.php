@foreach ($children as $child)
    <option value="{{ $child->id }}" 
        {{ ($currentParent == $child->id) ? 'selected' : '' }}>
        {{ str_repeat('— ', $level) . $child->name }}
    </option>

    @if ($child->children->count())
        @include('partials.category-options', [
            'children' => $child->children,
            'level' => $level + 1,
            'currentParent' => $currentParent
        ])
    @endif
@endforeach
