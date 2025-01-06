SELECT 'hello world...' as '';

DROP TABLE IF EXISTS likes;
SELECT 'Dropped likes table...' as '';

DROP TABLE IF EXISTS posts;
SELECT 'Dropped posts table...' as '';

DROP TABLE IF EXISTS users;
SELECT 'Dropped users table...' as '';

-- Opret users tabel
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    salt VARCHAR(40) NOT NULL,
    profile_pic VARCHAR(255) NOT NULL,
    bio VARCHAR(250) NOT NULL,
    location VARCHAR(50) NOT NULL,
    website VARCHAR(100) NOT NULL,
    followers INT DEFAULT 0,
    following INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
SELECT 'Created users table...' as '';

-- Opret posts tabel
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    content VARCHAR(255),
    image VARCHAR(255),
    video VARCHAR(255),
    likes INT DEFAULT 0,
    shares INT DEFAULT 0,
    comments INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
SELECT 'Created posts table...' as '';

-- Opret likes tabel
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    post_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (post_id) REFERENCES posts(id)
);
SELECT 'Created likes table...' as '';

SELECT 'Database initialization completed!' as '';