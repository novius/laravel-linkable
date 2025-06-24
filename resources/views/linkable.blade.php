<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{ state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }} }"
        x-on:linkable-selected="state = $event.detail.item"
    >
        <input x-model="state" type="hidden"/>

        <livewire:laravel-linkable::linkable-fields :linkableClasses="$linkableClasses" :initState="$getState()"/>
    </div>
</x-dynamic-component>
