<?php

namespace App\Dictionary;

/**
 * Class ParamsDictionary
 * @package App\Dictionary
 */
class ParamsDictionary
{
    public const TYPE_INT = 'int';
    public const TYPE_STRING = 'string';
    public const TYPE_ARRAY = 'array';

    public const PARAM_IMAGE            = 'image';
    public const PARAM_ADDITIONAL_IMAGE = 'additionalImage';
    public const PARAM_MODEL            = 'model';
    public const PARAM_CATEGORIES       = 'categories';

    public const PARAM_TYPE_TEXT     = 'text';
    public const PARAM_TYPE_PHOTO    = 'photo';
    public const PARAM_TYPE_LONGTEXT = 'longtext';
    public const PARAM_TYPE_MULTI    = 'multiselect';

    public const PARAM_TYPES_WITH_VARIANTS = [
        self::PARAM_TYPE_MULTI
    ];
}