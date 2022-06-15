/* PostgreSQL migration */
CREATE TABLE modules(
    id SERIAL,
    name TEXT,
    enable INTEGER,
    environment TEXT NOT NULL DEFAULT 'any',
    priority INT NOT NULL DEFAULT 1,

    PRIMARY KEY(id)
);

INSERT INTO modules (name, enable, environment, priority) 
VALUES 
    ('CacheHandler', 1, 'cli', 1),
    ('ModulesHandler', 1, 'any', 2),
    ('AuthModule', 1, 'web', 3);

CREATE TABLE cache(
    id SERIAL,

    key TEXT,
    value TEXT,

    expiration INTEGER,

    date_add TIMESTAMP NOT NULL DEFAULT NOW(),

    PRIMARY KEY(id)
);

CREATE TABLE config(
    id SERIAL, 
    key TEXT, 
    value TEXT, 

    date_add TIMESTAMP NOT NULL DEFAULT NOW(), 
    date_update TIMESTAMP NOT NULL DEFAULT NOW(), 

    PRIMARY KEY(key)
);

CREATE TABLE users (
    id SERIAL, 

    active INTEGER NOT NULL DEFAULT 1,

    name TEXT, 
    login TEXT NOT NULL UNIQUE,
    secondname TEXT NOT NULL DEFAULT '', 
    thirdname TEXT NOT NULL DEFAULT '', 
    password TEXT NOT NULL,

    email TEXT NOT NULL UNIQUE, 
    phone TEXT NOT NULL DEFAULT '', 

    date_add TIMESTAMP NOT NULL DEFAULT NOW(), 
    date_upd TIMESTAMP NOT NULL DEFAULT NOW(), 

    last_login TIMESTAMP NOT NULL DEFAULT NOW(),

    PRIMARY KEY(id)
);

INSERT INTO users (name, email, login, password) VALUES ('Guest', 'info@site.com', 'guest', '');

CREATE TABLE access (
    id SERIAL, 
    title TEXT NOT NULL, 
    description TEXT NOT NULL DEFAULT '',

    PRIMARY KEY(id)
);

INSERT INTO access (title, description) VALUES 
    ('admin','User who has full access on the site'), 
    ('employee', 'User who can manipulate content'), 
    ('user', 'User who can view site content'), 
    ('guest', 'Guest user');

CREATE TABLE user_access (
    user_id INTEGER NOT NULL, 
    access INTEGER NOT NULL, 

    CONSTRAINT fk_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE, 
    CONSTRAINT fk_access FOREIGN KEY(access) REFERENCES access(id) ON DELETE CASCADE,

    PRIMARY KEY(user_id)
);
/**
 * User access for guest 
*/
INSERT INTO user_access (user_id, access) VALUES (1, 4);

CREATE TABLE user_session (
    user_id INTEGER NOT NULL, 
    token TEXT NOT NULL, 
    expiration TIMESTAMP NOT NULL DEFAULT NOW() + INTERVAL '1 day', 
    
    CONSTRAINT fk_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    PRIMARY KEY (user_id)
);

CREATE TABLE country(
    id SERIAL,
    title TEXT NOT NULL,
    iso TEXT NOT NULL,

    PRIMARY KEY(id)
);
