@props([
    'paginator' => null,
])

<div class="space-y-4">
    <div class="hidden md:block">
        <flux:card class="overflow-hidden rounded-2xl p-6">
            {{ $desktop }}
        </flux:card>
    </div>

    <div class="space-y-3 md:hidden">
        {{ $mobile }}
    </div>

    @if ($paginator instanceof \Illuminate\Contracts\Pagination\Paginator && $paginator->hasPages())
        <flux:card class="rounded-2xl p-4 md:hidden mb-[calc(env(safe-area-inset-bottom)+0.5rem)]">
            {{ $paginator->links(data: ['scrollTo' => false]) }}
        </flux:card>
    @endif
</div>
