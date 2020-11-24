/**
 * Install SQL
 * Required if the module adds programs to other modules
 * Required if the module has menu entries
 * - Add profile exceptions for the module to appear in the menu
 * - Add program config options if any (to every schools)
 * - Add module specific tables (and their eventual sequences & indexes)
 *   if any: see rosariosis.sql file for Loginpros
 *
 */

/**
 * profile_exceptions Table
 *
 * profile_id:
 * - 0: student
 * - 1: admin
 * - 2: teacher
 * - 3: parent
 * modname: should match the Menu.php entries
 * can_use: 'Y'
 * can_edit: 'Y' or null (generally null for non admins)
 */

-- Eleve
INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 0, 'Loginpro/ListeEleves.php', 'Y', null
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Loginpro/ListeEleves.php'
    AND profile_id=0);

-- Admin
INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'Loginpro/ListeEleves.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Loginpro/ListeEleves.php'
    AND profile_id=1);
INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'Loginpro/Programmes.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Loginpro/Programmes.php'
    AND profile_id=1);

-- Teacher
INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 2, 'Loginpro/ListeEleves.php', 'Y', null
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Loginpro/ListeEleves.php'
    AND profile_id=2);
INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 2, 'Loginpro/Programmes.php', 'Y', null
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Loginpro/Programmes.php'
    AND profile_id=2);

-- Parent
INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 3, 'Loginpro/ListeEleves.php', 'Y', null
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Loginpro/ListeEleves.php'
    AND profile_id=3);