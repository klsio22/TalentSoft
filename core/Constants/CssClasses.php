<?php

namespace Core\Constants;

class CssClasses
{
    // forms
    public const INPUT_BASE = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 '
                            . 'focus:ring-blue-500 focus:border-transparent';
    public const INPUT_ERROR = 'w-full px-3 py-2 border border-red-300 rounded-md focus:outline-none focus:ring-2 '
                             . 'focus:ring-red-500 focus:border-transparent bg-red-50';
    public const INPUT_ERROR_CLASSES = 'border-red-300 focus:ring-red-500';
    public const SELECT_BASE = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 '
                             . 'focus:ring-blue-500 focus:border-transparent';
    public const LABEL_BASE = 'block text-sm font-medium text-gray-700 mb-2';

    // buttons
    public const BUTTON_PRIMARY = 'bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg';
    public const BUTTON_SECONDARY = 'bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg';
    public const BUTTON_SUCCESS = 'bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg';
    public const BUTTON_DANGER = 'bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg';
    public const BUTTON_WARNING = 'bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg';

    // Cards e containers
    public const CARD_BASE = 'bg-white rounded-lg shadow-sm border border-gray-200';
    public const CARD_HEADER = 'p-6 border-b border-gray-200';
    public const CARD_BODY = 'p-6';

    // Layout
    public const CONTAINER_MAX_WIDTH = 'max-w-7xl mx-auto';
    public const FLEX_BETWEEN = 'flex justify-between items-center';
    public const FLEX_CENTER = 'flex items-center justify-center';

    // Ações da tabela
    public const ACTION_VIEW = 'bg-blue-100 text-blue-600 hover:bg-blue-200 px-3 py-1 rounded-md text-sm';
    public const ACTION_EDIT = 'bg-yellow-100 text-yellow-600 hover:bg-yellow-200 px-3 py-1 rounded-md text-sm';
    public const ACTION_DELETE = 'bg-red-100 text-red-600 hover:bg-red-200 px-3 py-1 rounded-md text-sm';

    // Paginação
    public const PAGINATION_LINK = 'px-3 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md';
    public const PAGINATION_ACTIVE = 'px-3 py-2 text-sm bg-blue-600 text-white rounded-md';
    public const PAGINATION_DISABLED = 'px-3 py-2 text-sm text-gray-300 rounded-md cursor-not-allowed';
    public const PAGINATION_NAV_BUTTON = 'px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-50 '
                                       . 'rounded-md flex items-center';

    // Status badges
    public const STATUS_ACTIVE = 'inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 '
                               . 'text-green-800';
    public const STATUS_INACTIVE = 'inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 '
                                 . 'text-gray-800';

    // Alertas
    public const ALERT_SUCCESS = 'bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md';
    public const ALERT_ERROR = 'bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md';
    public const ALERT_WARNING = 'bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-md';
    public const ALERT_INFO = 'bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-md';

    // Tabelas
    public const TABLE_BASE = 'min-w-full divide-y divide-gray-200';
    public const TABLE_HEADER = 'bg-gray-50';
    public const TABLE_HEADER_CELL = 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase '
                                    . 'tracking-wider';
    public const TABLE_ROW = 'hover:bg-gray-50';
    public const TABLE_CELL = 'px-6 py-4 whitespace-nowrap text-sm text-gray-900';
    public const TABLE_CELL_SECONDARY = 'px-6 py-4 whitespace-nowrap text-sm text-gray-500';

    /**
     * Retorna a classe CSS para input baseada no estado de erro
     */
    public static function inputClass(bool $hasError = false): string
    {
        return $hasError ? self::INPUT_ERROR : self::INPUT_BASE;
    }

    /**
     * Retorna as classes adicionais para input com erro
     */
    public static function inputErrorClasses(): string
    {
        return self::INPUT_ERROR_CLASSES;
    }
}
