DROP DATABASE dawg_gui;

CREATE DATABASE dawg_gui;
USE dawg_gui;

CREATE TABLE users (
    user_id int NOT NULL,
    user_fname varchar(255) NOT NULL,
    user_lname varchar(255),
    PRIMARY KEY(user_id)
);

CREATE TABLE channel (
    channel_id int NOT NULL AUTO_INCREMENT,
    channel_name varchar(255) NOT NULL,
    PRIMARY KEY (channel_id)
);

CREATE TABLE admin (
    user_id int NOT NULL AUTO_INCREMENT,
    channel_id int NOT NULL,
    PRIMARY KEY (user_id, channel_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (channel_id) REFERENCES channel(channel_id)
);

CREATE TABLE image (
    image_id int NOT NULL AUTO_INCREMENT,
    src varchar(255) NOT NULL,
    PRIMARY KEY (image_id)
);

CREATE TABLE post (
    post_id int NOT NULL AUTO_INCREMENT,
    user_id int NOT NULL,
    image_id int NOT NULL,
    channel_id int NOT NULL,
    caption varchar(255),
    PRIMARY KEY (post_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (image_id) REFERENCES image(image_id),
    FOREIGN KEY (channel_id) REFERENCES channel(channel_id)
);

CREATE TABLE status (
    post_id int NOT NULL,
    user_id int NOT NULL,
    reaction int,
    comment varchar(255),
    PRIMARY KEY (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES post(post_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE subscribe (
    user_id int NOT NULL,
    channel_id int NOT NULL,
    PRIMARY KEY (user_id, channel_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (channel_id) REFERENCES channel(channel_id)
);
