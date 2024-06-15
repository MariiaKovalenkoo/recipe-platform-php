CREATE DATABASE IF NOT EXISTS RecipesPlatform;

USE RecipesPlatform;

CREATE TABLE IF NOT EXISTS User (
    userId INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    firstName VARCHAR(50),
    lastName VARCHAR(50),
    email VARCHAR(100) UNIQUE
    );

CREATE TABLE IF NOT EXISTS Recipe (
    recipeId INT AUTO_INCREMENT PRIMARY KEY,
    recipeName VARCHAR(100) NOT NULL,
    ingredients TEXT NOT NULL,
    description TEXT,
    instructions TEXT NOT NULL,
    isPublic BOOLEAN DEFAULT FALSE,
    mealType VARCHAR(50) NOT NULL,
    dietaryPreference VARCHAR(50) DEFAULT 'NOT SPECIFIED',
    cuisineType VARCHAR(50) DEFAULT 'NOT SPECIFIED',
    userId INT NOT NULL,
    imgPath VARCHAR(100),

    FOREIGN KEY (userId) REFERENCES User(userId)
 );

CREATE TABLE IF NOT EXISTS UserFavorite (
                                            favoriteId INT AUTO_INCREMENT PRIMARY KEY,
                                            userId INT,
                                            recipeId INT,
                                            addedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                            FOREIGN KEY (userId) REFERENCES User(userId),
                                            FOREIGN KEY (recipeId) REFERENCES Recipe(recipeId)
);