{{-- resources/views/components/admin/bilingual-field.blade.php --}}
@props([
    'type' => 'text',
    'name',
    'label' => null,
    'valueAr' => null,
    'valueEn' => null,
])

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-admin.field
        :type="$type"
        :name="$name.'_ar'"
        :label="$label ? $label.' ('.__('Arabic').')' : null"
        :value="$valueAr"
        dir="rtl"
    />
    <x-admin.field
        :type="$type"
        :name="$name.'_en'"
        :label="$label ? $label.' ('.__('English').')' : null"
        :value="$valueEn"
    />
</div>
