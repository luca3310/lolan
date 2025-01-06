DROP TABLE IF EXISTS posts;
SELECT 'Dropped posts table...' as '';

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    content VARCHAR(255)
);

SELECT 'Database initialization completed!' as '';
