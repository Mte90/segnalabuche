<?php

namespace App\Services;

class TemplateResolver
{
    public static function resolve(string $templateName): string
    {
        $customPath = "mail.guasto_custom.{$templateName}";
        $defaultPath = "mail.{$templateName}";

        if (view()->exists($customPath)) {
            return $customPath;
        }

        return $defaultPath;
    }
}
