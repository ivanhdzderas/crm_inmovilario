<?php

if (! function_exists('prospect_is_cancelled')) {
    function prospect_is_cancelled(?string $status): bool
    {
        return in_array($status, ['Cancelado', 'Descartado'], true);
    }
}

if (! function_exists('prospect_status_label')) {
    function prospect_status_label(?string $status): string
    {
        if ($status === 'Descartado') {
            return 'Cancelado';
        }

        return $status ?? '';
    }
}

if (! function_exists('prospect_cancellation_popover_attrs')) {
    /**
     * Atributos HTML para popover Bootstrap con motivo de cancelación.
     */
    function prospect_cancellation_popover_attrs(?string $reasonName, ?string $notes, ?string $cancelledAt): string
    {
        if (empty($reasonName)) {
            return '';
        }

        $content = '<strong>' . esc($reasonName) . '</strong>';
        if (! empty($notes)) {
            $content .= '<br><span class="text-muted">' . esc($notes) . '</span>';
        }
        if (! empty($cancelledAt)) {
            $content .= '<br><small>' . esc(date('d/m/Y H:i', strtotime($cancelledAt))) . '</small>';
        }

        return ' data-toggle="popover" data-trigger="hover focus" data-placement="top" data-html="true"'
            . ' title="Motivo de cancelación" data-content="' . esc($content, 'attr') . '"';
    }
}
