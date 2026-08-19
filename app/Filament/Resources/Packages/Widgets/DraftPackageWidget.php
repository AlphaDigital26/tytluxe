<?php

namespace App\Filament\Resources\Packages\Widgets;

use Filament\Widgets\Widget;

class DraftPackageWidget extends Widget
{
    protected string $view = 'filament.resources.packages.widgets.draft-package-widget';

    public ?array $draft = null;

    public function mount(): void
    {
        $this->draft = cache()->get('draft_package_' . auth()->id());
    }

    public function clearDraft(): void
    {
        cache()->forget('draft_package_' . auth()->id());
        $this->draft = null;
    }
}
