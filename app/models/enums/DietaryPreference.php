<?php

namespace Models\enums;

enum DietaryPreference : string
{
    case NOT_SPECIFIED = 'Not Specified';
    case VEGETARIAN = 'Vegetarian';
    case VEGAN = 'Vegan';
    case GLUTEN_FREE = 'Gluten Free';
    case KETO = 'Keto';
}
