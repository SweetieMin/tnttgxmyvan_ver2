@props([
    'categoryBarChart' => [],
])

<flux:card class="rounded-2xl p-6">
    <div class="flex flex-col gap-2">
        <flux:heading size="lg">{{ __('Income vs expense by category') }}</flux:heading>
        <flux:text>{{ __('Compare how each category contributes to cash inflow and outflow.') }}</flux:text>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-4 text-sm">
        <div class="inline-flex items-center gap-2 text-zinc-600 dark:text-zinc-300">
            <span class="size-2.5 rounded-full bg-emerald-500"></span>
            <span>{{ __('Income') }}</span>
        </div>
        <div class="inline-flex items-center gap-2 text-zinc-600 dark:text-zinc-300">
            <span class="size-2.5 rounded-full bg-rose-500"></span>
            <span>{{ __('Expense') }}</span>
        </div>
    </div>

    @if (count($categoryBarChart) > 1)
        <div class="mt-4">
            <flux:chart class="h-80 w-full min-w-0" :value="$categoryBarChart">
                <flux:chart.svg gutter="14 18 38 58">
                    <flux:chart.axis axis="x" field="category">
                        <flux:chart.axis.tick class="[:where(&)]:text-[11px]" />
                    </flux:chart.axis>

                    <flux:chart.axis axis="y" :field="null">
                        <flux:chart.axis.grid />
                        <flux:chart.axis.line />
                        <flux:chart.axis.tick />
                    </flux:chart.axis>

                    <flux:chart.group gap="10">
                        <flux:chart.bar field="income" width="38%" radius="6" class="text-emerald-500 dark:text-emerald-400" />
                        <flux:chart.bar field="expense" width="38%" radius="6" class="text-rose-500 dark:text-rose-400" />
                    </flux:chart.group>

                    <flux:chart.cursor />
                </flux:chart.svg>
            </flux:chart>
        </div>
    @else
        <flux:callout class="mt-4" color="sky" icon="information-circle"
            :heading="__('Need at least two category data points to render this chart.')" />
    @endif
</flux:card>
