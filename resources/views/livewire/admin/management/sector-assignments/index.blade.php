<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Sector assignments')"
        :sub-title="__('Assign sector leaders and movement staff so attendance permissions follow the correct sector.')"
        icon="flag"
    />

    <x-management-module-placeholder
        :heading="__('Sector assignments')"
        :description="__('This module will manage sector-based permissions for leaders, vice leaders, and movement staff.')"
        :highlights="[
            __('Assign staff to sectors for a specific academic year.'),
            __('Control which leaders can scan attendance by sector.'),
            __('Keep unit-wide permissions for unit leaders and vice leaders.'),
        ]"
    />
</section>
