<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/util/placeholders.php'); }

if (!function_exists('applyConnectionPlaceholders')) {
        function applyConnectionPlaceholders($template, $subscriptionLink, $configList)
        {
            $trimmedSubscription = trim((string) $subscriptionLink);
            $trimmedConfigList = trim((string) $configList);

            $connectionSections = [];
            $configSection = '';
            $linksSection = '';

            if ($trimmedSubscription !== '') {
                $configSection = "🔗 لینک اتصال:\n\n<code>{$trimmedSubscription}</code>";
                $connectionSections['config'] = $configSection;
            }

            if ($trimmedConfigList !== '') {
                $linksSection = "🔐 کانفیگ اشتراک :\n\n<code>{$trimmedConfigList}</code>";
                $connectionSections['links'] = $linksSection;
            }

            $connectionLinksBlock = implode("\n\n", array_values($connectionSections));
            if ($connectionLinksBlock !== '') {
                $connectionLinksBlock .= "\n";
            }

            $hasConnectionLinksPlaceholder = strpos($template, '{connection_links}') !== false;
            $hasConfigPlaceholder = strpos($template, '{config}') !== false;
            $hasLinksPlaceholder = strpos($template, '{links}') !== false;

            $placeholderLabels = [
                '{config}' => [
                    '🔗 لینک اتصال:',
                    '🔗 لینک اتصال :',
                    'لینک اتصال:',
                    'لینک اتصال :',
                ],
                '{links}' => [
                    '🔐 کانفیگ اشتراک:',
                    '🔐 کانفیگ اشتراک :',
                    'لینک اشتراک:',
                    'لینک اشتراک :',
                ],
            ];

            $replacePlaceholder = function ($templateValue, $placeholder, $replacement) use ($placeholderLabels) {
                $wrappedPlaceholder = "<code>{$placeholder}</code>";
                $labels = $placeholderLabels[$placeholder] ?? [];
                $placeholderPattern = '(?:' . preg_quote($placeholder, '/') . '|' . preg_quote($wrappedPlaceholder, '/') . ')';

                foreach ($labels as $label) {
                    $labelPattern = preg_quote($label, '/');
                    $pattern = '/(^|\R)[^\S\r\n]*' . $labelPattern . '[^\S\r\n]*(?:\r?\n)?[^\S\r\n]*' . $placeholderPattern . '/u';
                    $updatedTemplate = preg_replace($pattern, '$1' . $replacement, $templateValue, 1, $count);
                    if ($count > 0) {
                        return $updatedTemplate;
                    }
                }

                if (strpos($templateValue, $wrappedPlaceholder) !== false) {
                    return str_replace($wrappedPlaceholder, $replacement, $templateValue);
                }

                return str_replace($placeholder, $replacement, $templateValue);
            };

            if ($hasConnectionLinksPlaceholder) {
                $template = str_replace('{connection_links}', $connectionLinksBlock, $template);

                if ($hasConfigPlaceholder) {
                    $configReplacement = $configSection;
                    if ($configReplacement !== '' && $linksSection !== '') {
                        $configReplacement .= "\n\n";
                    }
                    $template = $replacePlaceholder($template, '{config}', $configReplacement);
                }

                if ($hasLinksPlaceholder) {
                    $template = $replacePlaceholder($template, '{links}', $linksSection);
                }
            } elseif ($hasConfigPlaceholder || $hasLinksPlaceholder) {
                if ($hasConfigPlaceholder && $hasLinksPlaceholder) {
                    $configReplacement = $configSection;
                    if ($configReplacement !== '' && $linksSection !== '') {
                        $configReplacement .= "\n\n";
                    }

                    $template = $replacePlaceholder($template, '{config}', $configReplacement);
                    $template = $replacePlaceholder($template, '{links}', $linksSection);
                } elseif ($hasConfigPlaceholder) {
                    $template = $replacePlaceholder($template, '{config}', $connectionLinksBlock);
                } else {
                    $template = $replacePlaceholder($template, '{links}', $connectionLinksBlock);
                }
            }

            if (strpos($template, '{links2}') !== false) {
                $template = str_replace('{links2}', $trimmedSubscription, $template);
            }

            return $template;
        }
}
