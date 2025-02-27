<?php

namespace Models\enums;

enum DietaryPreference : string
{
    case VEGETARIAN = 'Vegetarian';
    case VEGAN = 'Vegan';
    case GLUTEN_FREE = 'Gluten Free';
    case KETO = 'Keto';
    case NOT_SPECIFIED = 'Not Specified';
}
