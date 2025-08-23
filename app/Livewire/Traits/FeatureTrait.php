<?php

namespace App\Livewire\Traits;

use App\Enums\Guild\SettingTypeEnum;
use App\Models\Guild;

trait FeatureTrait
{
    public function isFeatureEnabled(Guild $guild, SettingTypeEnum $feature): bool
    {
        return getSettingValue($guild, $feature->value, false);
    }

    public function ensureFeatureEnabled(Guild $guild, SettingTypeEnum $feature): bool
    {
        if (! $this->isFeatureEnabled($guild, $feature)) {
            $this->toast()
                ->error('A funkció nincs engedélyezve ezen a szerveren.')
                ->send();

            return false;
        }

        return true;
    }
}
