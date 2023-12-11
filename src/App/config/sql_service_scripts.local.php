<?php

$tableNames = [
    "users" => "users",
    "categories" => "categories",
    "category_compositions" => "category_compositions",
    "category_composition_members" => "category_composition_members",
    "expenses" => "expenses",
];

return [
    "tableNames" => $tableNames,
    "createTableUsersIfNotExist" => "
        CREATE TABLE IF NOT EXISTS ".$tableNames["users"]." (
            id INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
            username VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            displayname VARCHAR(255) NOT NULL,
            lastLogin TIMESTAMP NOT NULL,
            UNIQUE (username)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
    ",

    "createTableCategoriesIfExist" => "
        CREATE TABLE IF NOT EXISTS ".$tableNames["categories"]." (
            id INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            created TIMESTAMP NOT NULL,
            userId INT UNSIGNED NOT NULL,
            UNIQUE (title),
            CONSTRAINT fk_categories__users FOREIGN KEY(userId) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
    ",

    "createTableCategoryCompositionsIfExist" => "
        CREATE TABLE IF NOT EXISTS ".$tableNames["category_compositions"]." (
            id INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
            created TIMESTAMP NOT NULL,
            userId INT UNSIGNED NOT NULL,
            CONSTRAINT fk_category_compositions__users FOREIGN KEY(userId) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
    ",

    "createTableCategoryCompositionMembersIfExist" => "
        CREATE TABLE IF NOT EXISTS ".$tableNames["category_composition_members"]." (
            id INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
            categoryId INT UNSIGNED NOT NULL,
            categoryCompositionId INT UNSIGNED NOT NULL,
            UNIQUE (categoryId, categoryCompositionId),
            CONSTRAINT fk_category_compositions_members__categories FOREIGN KEY(categoryId) REFERENCES categories(id),
            CONSTRAINT fk_category_composition_members__category_compositions FOREIGN KEY(categoryCompositionId) REFERENCES category_compositions(Id)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
    ",

    "createTableExpensesIfExist" => "
        CREATE TABLE IF NOT EXISTS ".$tableNames["expenses"]." (
            id INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
            userId INT UNSIGNED NOT NULL,
            categoryCompositionId INT UNSIGNED NOT NULL,
            price FLOAT NOT NULL,
            created TIMESTAMP NOT NULL,
            metatext VARCHAR(255) NOT NULL,
            CONSTRAINT fk_expenses__users FOREIGN KEY(userId) REFERENCES users(id),
            CONSTRAINT fk_expenses__category_compositions FOREIGN KEY(categoryCompositionId) REFERENCES category_compositions(Id)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
    ",
];