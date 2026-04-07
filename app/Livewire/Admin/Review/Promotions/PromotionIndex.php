<?php

namespace App\Livewire\Admin\Review\Promotions;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Xét lên lớp')]
class PromotionIndex extends Component
{
    public function render(): View
    {
        return view('livewire.admin.review.promotions.index');
    }
}
