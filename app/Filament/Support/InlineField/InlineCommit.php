<?php

declare(strict_types=1);

namespace App\Filament\Support\InlineField;

use App\Enums\CustomFieldType;

enum InlineCommit
{
    case OnChange;
    case OnEnterOrBlur;
    case OnConfirm;
    case InModal;

    public static function forType(CustomFieldType $type): ?self
    {
        if (! $type->isInlineEditable()) {
            return null;
        }

        if ($type->opensInModal()) {
            return self::InModal;
        }

        if ($type->requiresExplicitConfirm()) {
            return self::OnConfirm;
        }

        if ($type->savesOnChange()) {
            return self::OnChange;
        }

        return self::OnEnterOrBlur;
    }
}
