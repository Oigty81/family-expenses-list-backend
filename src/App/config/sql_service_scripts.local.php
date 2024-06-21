<?php

$createTable_users = <<<ct_users
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    displayname VARCHAR(255) NOT NULL,
    lastLogin TIMESTAMP NOT NULL,
    UNIQUE (username)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
ct_users;

$createTable_categories = <<<ct_categories
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    created TIMESTAMP NOT NULL,
    userId INT UNSIGNED NOT NULL,
    UNIQUE (title),
    CONSTRAINT fk_categories__users FOREIGN KEY(userId) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
ct_categories;

$createTable_category_compositions = <<<ct_category_compositions
CREATE TABLE IF NOT EXISTS category_compositions (
    id INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    created TIMESTAMP NOT NULL,
    userId INT UNSIGNED NOT NULL,
    CONSTRAINT fk_category_compositions__users FOREIGN KEY(userId) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
ct_category_compositions;

$createTable_category_composition_members = <<<ct_category_composition_members
CREATE TABLE IF NOT EXISTS category_composition_members (
    id INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    categoryId INT UNSIGNED NOT NULL,
    categoryCompositionId INT UNSIGNED NOT NULL,
    UNIQUE (categoryId, categoryCompositionId),
    CONSTRAINT fk_category_compositions_members__categories FOREIGN KEY(categoryId) REFERENCES categories(id),
    CONSTRAINT fk_category_composition_members__category_compositions FOREIGN KEY(categoryCompositionId) REFERENCES category_compositions(Id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
ct_category_composition_members;

$createTable_expenses = <<<ct_expenses
CREATE TABLE IF NOT EXISTS expenses (
    id INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    userId INT UNSIGNED NOT NULL,
    categoryCompositionId INT UNSIGNED NOT NULL,
    price FLOAT NOT NULL,
    created TIMESTAMP NOT NULL,
    metatext VARCHAR(255) NOT NULL,
    CONSTRAINT fk_expenses__users FOREIGN KEY(userId) REFERENCES users(id),
    CONSTRAINT fk_expenses__category_compositions FOREIGN KEY(categoryCompositionId) REFERENCES category_compositions(Id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
ct_expenses;

return [
    "createTable_users_IfNotExist" => $createTable_users,
    "createTable_categories_IfNotExist" => $createTable_categories,
    "createTable_category_compositions_IfNotExist" => $createTable_category_compositions,
    "createTable_category_composition_members_IfNotExist" => $createTable_category_composition_members,
    "createTable_expenses_IfNotExist" => $createTable_expenses,
];
