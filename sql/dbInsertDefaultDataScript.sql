USE RecipesPlatform;

    // db name
    // passwords hashing
    // is admin
    // status recipe
    // id instead of user id
    // comment table
    // remove old data?
    // add footer

INSERT INTO User (username, password, firstName, lastName, email)
VALUES
    ('user1', SHA2('password1', 256), 'John', 'Doe', 'john@example.com'),
    ('user2', SHA2('password2', 256), 'Jane', 'Smith', 'jane@example.com'),
    ('user3', SHA2('password3', 256), 'Mike', 'Johnson', 'mike@example.com'),
    ('user4', SHA2('password4', 256), 'System', 'User', 'system@example.com');


INSERT INTO Recipe (recipeName, ingredients, description, instructions, isPublic, mealType, dietaryPreference, cuisineType, userId, imgPath)
VALUES
    ('Pancakes', 'Flour, eggs, milk, sugar', 'Delicious pancakes for breakfast', 'Mix all ingredients, fry on a pan', FALSE, 'BREAKFAST', 'NOT SPECIFIED', 'NOT SPECIFIED', 1, '/img/recipes/pancakes.jpg'),
    ('Spaghetti Carbonara', 'Spaghetti, eggs, pancetta, black pepper, Parmesan cheese', 'Classic Italian pasta dish', 'Boil spaghetti, fry pancetta, mix eggs and cheese, toss together', FALSE, 'DINNER', 'NOT SPECIFIED', 'ITALIAN', 1, '/img/recipes/carbonara.jpg'),
    ('Vegetable Stir-Fry', 'Broccoli, Carrots, Bell Peppers, Soy Sauce', 'Healthy and tasty stir-fry.', '1. Chop vegetables. 2. Stir-fry with soy sauce.', FALSE, 'DINNER', 'VEGETARIAN', 'ASIAN', 2, '/img/recipes/veggies.jpg'),
    ('Chocolate Cake', 'Flour, Sugar, Cocoa Powder, Eggs', 'Decadent chocolate cake.', '1. Mix flour, sugar, cocoa powder, and eggs. 2. Bake in the oven.', TRUE, 'DESSERT', 'NOT SPECIFIED', 'NOT SPECIFIED', 3, '/img/recipes/cake.jpg'),
    ('Grilled Salmon', 'Salmon fillet, olive oil, lemon, garlic, salt, pepper', 'Healthy and flavorful grilled salmon', '1. Marinate salmon with olive oil, lemon, garlic, salt, and pepper. 2. Grill until cooked.', TRUE, 'DINNER', 'NOT SPECIFIED', 'NOT SPECIFIED', 2, '/img/recipes/salmon.jpg'),
    ('Caprese Salad', 'Tomatoes, fresh mozzarella, basil, balsamic glaze', 'Refreshing Italian salad', '1. Slice tomatoes and mozzarella. 2. Arrange on a plate with fresh basil. 3. Drizzle with balsamic glaze.', TRUE, 'LUNCH', 'VEGETARIAN', 'ITALIAN', 3, '/img/recipes/caprese.jpg'),
    ('Chicken Alfredo Pasta', 'Chicken breast, fettuccine pasta, heavy cream, Parmesan cheese', 'Creamy and satisfying pasta dish', '1. Cook pasta. 2. Sauté chicken. 3. Mix cooked pasta, chicken, heavy cream, and Parmesan cheese.', TRUE, 'DINNER', 'NOT SPECIFIED', 'NOT SPECIFIED', 4 , '/img/recipes/alfredo.jpg'),
    ('Berry Smoothie', 'Mixed berries, yogurt, banana, honey', 'Refreshing and nutritious smoothie', '1. Blend mixed berries, yogurt, banana, and honey until smooth. 2. Enjoy!', TRUE, 'BREAKFAST', 'VEGETARIAN', 'NOT SPECIFIED', 2, '/img/recipes/smoothie.jpg');


INSERT INTO Recipe (recipeName, ingredients, description, instructions, isPublic, mealType, dietaryPreference, cuisineType, userId, imgPath)
VALUES
    ('Quinoa Salad', 'Quinoa, cucumbers, tomatoes, feta cheese, olives, lemon vinaigrette', 'Healthy and refreshing salad with a tangy dressing', '1. Cook quinoa. 2. Chop vegetables. 3. Mix all ingredients with lemon vinaigrette.', TRUE, 'LUNCH', 'VEGETARIAN', 'ITALIAN', 1, '/img/recipes/quinoa-salad.jpg'),
    ('Banana Bread', 'Bananas, flour, sugar, eggs, butter, baking soda', 'Moist and delicious bread made with overripe bananas', '1. Mash bananas. 2. Mix with melted butter. 3. Blend in eggs, flour, and sugar. 4. Bake.', TRUE, 'DESSERT', 'VEGETARIAN', 'NOT SPECIFIED', 2, '/img/recipes/banana-bread.jpg'),
    ('Tofu Curry', 'Tofu, coconut milk, curry paste, mixed vegetables', 'Spicy and aromatic curry with tofu and vegetables', '1. Fry tofu until golden. 2. Sauté vegetables. 3. Mix in curry paste and coconut milk. Simmer.', TRUE, 'DINNER', 'VEGAN', 'ASIAN', 1, '/img/recipes/tofucurry.jpg'),
    ('Avocado Toast', 'Avocado, whole grain bread, cherry tomatoes, radishes, salt, pepper', 'Simple and healthy avocado toast with fresh veggies', '1. Mash avocado. 2. Spread on toasted bread. 3. Top with sliced veggies. Season.', TRUE, 'BREAKFAST', 'VEGAN', 'NOT SPECIFIED', 1, '/img/recipes/avocado-toast.jpg'),
    ('Mushroom Risotto', 'Arborio rice, mushrooms, chicken broth, Parmesan cheese, white wine, onions, garlic', 'Creamy Italian rice dish with savory mushrooms', '1. Sauté mushrooms, onions, and garlic. 2. Stir in rice until toasted. 3. Gradually add broth and wine, stirring until creamy.', TRUE, 'DINNER', 'VEGETARIAN', 'ITALIAN', 2, '/img/recipes/mushroom-risotto.jpg'),
    ('Pumpkin Soup', 'Pumpkin, vegetable broth, onion, cream, nutmeg', 'Smooth and comforting pumpkin soup with a hint of nutmeg', '1. Sauté onion. 2. Add pumpkin and broth, simmer. 3. Blend until smooth. Stir in cream.', TRUE, 'LUNCH', 'VEGETARIAN', 'NOT SPECIFIED', 1, '/img/recipes/pumpkin-soup.jpg'),
    ('Greek Yogurt Parfait', 'Greek yogurt, granola, honey, mixed berries', 'Layered parfait with yogurt, fruits, and granola', '1. Layer Greek yogurt, granola, and berries in a glass. 2. Drizzle with honey.', TRUE, 'BREAKFAST', 'VEGETARIAN', 'NOT SPECIFIED', 2, '/img/recipes/yoghurt.jpg');
