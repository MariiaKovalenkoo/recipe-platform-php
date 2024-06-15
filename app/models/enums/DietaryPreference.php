<?php

namespace Models\enums;

enum DietaryPreference
{
    case NOT_SPECIFIED;
    case VEGETARIAN;
    case VEGAN;
    case GLUTEN_FREE;
    case KETO;
}
