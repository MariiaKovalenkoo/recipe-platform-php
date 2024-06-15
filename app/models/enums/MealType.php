<?php

namespace Models\enums;

enum MealType: string
{
    case BREAKFAST = 'Breakfast';
    case LUNCH = 'Lunch';
    case DINNER = 'Dinner';
    case SNACK = 'Snack';
    case DESSERT = 'Dessert';

}