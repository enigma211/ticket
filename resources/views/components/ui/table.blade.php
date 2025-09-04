<x-ui.card {{ $attributes }}>
    <div class="overflow-x-auto">
        <table class="w-full text-right">
            <thead class="bg-gray-100">
                {{ $header ?? '' }}
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
</x-ui.card>
