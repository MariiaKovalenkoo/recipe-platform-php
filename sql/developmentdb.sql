CREATE DATABASE IF NOT EXISTS developmentdb;

USE developmentdb;

CREATE TABLE IF NOT EXISTS User (
    id INT AUTO_INCREMENT PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    firstName VARCHAR(50),
    lastName VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    isAdmin BOOLEAN DEFAULT FALSE
    );

INSERT INTO User (email, password, firstName, lastName, isAdmin)
VALUES
    ('user@gmail.com', '$2y$10$DQlV0u9mFmtOWsOdxXX9H.4kgzEB3E8o97s.S.Pdy4klUAdBvtVh.', 'John', 'Doe', FALSE);

CREATE TABLE IF NOT EXISTS Recipe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    ingredients TEXT NOT NULL,
    description TEXT NOT NULL,
    instructions TEXT NOT NULL,
    mealType VARCHAR(50) NOT NULL,
    dietaryPreference VARCHAR(50) DEFAULT 'Not Specified',
    cuisineType VARCHAR(50) DEFAULT 'Not Specified',
    userId INT NOT NULL,
    imgPath VARCHAR(100),
    status VARCHAR(50) DEFAULT 'Pending',

    FOREIGN KEY (userId) REFERENCES User(id)
 );

INSERT INTO Recipe (`name`, ingredients, description, instructions, mealType, dietaryPreference, cuisineType, userId, imgPath, status)
VALUES
    ('Pancakes', 'Flour, eggs, milk, sugar', 'Delicious pancakes for breakfast', 'Mix all ingredients, fry on a pan', 'Breakfast', 'Not Specified', 'Not Specified', 1, '/img/recipes/pancakes.jpg', 'Approved'),
    ('Spaghetti Carbonara', 'Spaghetti, eggs, pancetta, black pepper, Parmesan cheese', 'Classic Italian pasta dish', 'Boil spaghetti, fry pancetta, mix eggs and cheese, toss together', 'Dinner', 'Not Specified', 'Italian', 1, '/img/recipes/carbonara.jpg', 'Approved'),
    ('Vegetable Stir-Fry', 'Broccoli, Carrots, Bell Peppers, Soy Sauce', 'Healthy and tasty stir-fry.', '1. Chop vegetables. 2. Stir-fry with soy sauce.', 'Dinner', 'Vegetarian', 'Asian', 1, '/img/recipes/veggies.jpg', 'Approved'),
    ('Chocolate Cake', 'Flour, Sugar, Cocoa Powder, Eggs', 'Decadent chocolate cake.', '1. Mix flour, sugar, cocoa powder, and eggs. 2. Bake in the oven.', 'Dessert', 'Not Specified', 'Not Specified', 1, '/img/recipes/cake.jpg', 'Approved'),
    ('Grilled Salmon', 'Salmon fillet, olive oil, lemon, garlic, salt, pepper', 'Healthy and flavorful grilled salmon', '1. Marinate salmon with olive oil, lemon, garlic, salt, and pepper. 2. Grill until cooked.', 'Dinner', 'Not Specified', 'Not Specified', 1, '/img/recipes/salmon.jpg', 'Approved'),
    ('Caprese Salad', 'Tomatoes, fresh mozzarella, basil, balsamic glaze', 'Refreshing Italian salad', '1. Slice tomatoes and mozzarella. 2. Arrange on a plate with fresh basil. 3. Drizzle with balsamic glaze.', 'Lunch', 'Vegetarian', 'Italian', 1, '/img/recipes/caprese.jpg', 'Approved'),
    ('Chicken Alfredo Pasta', 'Chicken breast, fettuccine pasta, heavy cream, Parmesan cheese', 'Creamy and satisfying pasta dish', '1. Cook pasta. 2. Sauté chicken. 3. Mix cooked pasta, chicken, heavy cream, and Parmesan cheese.', 'Dinner', 'Not Specified', 'Not Specified', 1 , '/img/recipes/alfredo.jpg', 'Approved'),
    ('Berry Smoothie', 'Mixed berries, yogurt, banana, honey', 'Refreshing and nutritious smoothie', '1. Blend mixed berries, yogurt, banana, and honey until smooth. 2. Enjoy!', 'Breakfast', 'Vegetarian', 'Not Specified', 1, '/img/recipes/smoothie.jpg', 'Approved');


INSERT INTO Recipe (`name`, ingredients, description, instructions, isPublic, mealType, dietaryPreference, cuisineType, userId, imgPath, status)
VALUES
    ('Quinoa Salad', 'Quinoa, cucumbers, tomatoes, feta cheese, olives, lemon vinaigrette', 'Healthy and refreshing salad with a tangy dressing', '1. Cook quinoa. 2. Chop vegetables. 3. Mix all ingredients with lemon vinaigrette.', 'Lunch', 'Vegetarian', 'Italian', 1, '/img/recipes/quinoa-salad.jpg', 'Pending'),
    ('Banana Bread', 'Bananas, flour, sugar, eggs, butter, baking soda', 'Moist and delicious bread made with overripe bananas', '1. Mash bananas. 2. Mix with melted butter. 3. Blend in eggs, flour, and sugar. 4. Bake.', 'Dessert', 'Vegetarian', 'Not Specified', 1, '/img/recipes/banana-bread.jpg', 'Pending'),
    ('Tofu Curry', 'Tofu, coconut milk, curry paste, mixed vegetables', 'Spicy and aromatic curry with tofu and vegetables', '1. Fry tofu until golden. 2. Sauté vegetables. 3. Mix in curry paste and coconut milk. Simmer.', 'Dinner', 'Vegan', 'Asian', 1, '/img/recipes/tofucurry.jpg', 'Pending'),
    ('Avocado Toast', 'Avocado, whole grain bread, cherry tomatoes, radishes, salt, pepper', 'Simple and healthy avocado toast with fresh veggies', '1. Mash avocado. 2. Spread on toasted bread. 3. Top with sliced veggies. Season.', 'Breakfast', 'Vegan', 'Not Specified', 1, '/img/recipes/avocado-toast.jpg', 'Pending'),
    ('Mushroom Risotto', 'Arborio rice, mushrooms, chicken broth, Parmesan cheese, white wine, onions, garlic', 'Creamy Italian rice dish with savory mushrooms', '1. Sauté mushrooms, onions, and garlic. 2. Stir in rice until toasted. 3. Gradually add broth and wine, stirring until creamy.', 'Dinner', 'Vegetarian', 'Italian', 1, '/img/recipes/mushroom-risotto.jpg', 'Pending'),
    ('Pumpkin Soup', 'Pumpkin, vegetable broth, onion, cream, nutmeg', 'Smooth and comforting pumpkin soup with a hint of nutmeg', '1. Sauté onion. 2. Add pumpkin and broth, simmer. 3. Blend until smooth. Stir in cream.', 'Lunch', 'Vegetarian', 'Not Specified', 1, '/img/recipes/pumpkin-soup.jpg', 'Pending'),
    ('Greek Yogurt Parfait', 'Greek yogurt, granola, honey, mixed berries', 'Layered parfait with yogurt, fruits, and granola', '1. Layer Greek yogurt, granola, and berries in a glass. 2. Drizzle with honey.', 'Breakfast', 'Vegetarian', 'Not Specified', 1, '/img/recipes/yoghurt.jpg', 'Pending');


CREATE TABLE IF NOT EXISTS FavoriteRecipe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId INT,
    recipeId INT,
    addedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES User(id),
    FOREIGN KEY (recipeId) REFERENCES Recipe(id)
);

CREATE TABLE IF NOT EXISTS Comment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId INT,
    recipeId INT,
    content TEXT NOT NULL,
    commentedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES User(id),
    FOREIGN KEY (recipeId) REFERENCES Recipe(id)
);