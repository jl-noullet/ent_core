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

-- Saisie ------------------------------------
-- Admin
INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'Notation/Saisie.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Notation/Saisie.php'
    AND profile_id=1);
-- Teacher
INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 2, 'Notation/Saisie.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Notation/Saisie.php'
    AND profile_id=2);

-- Etat d'avancement -------------------------
-- Admin
INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'Notation/Test.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Notation/Test.php'
    AND profile_id=1);

-- Competence --------------------------------
-- Admin
INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'Notation/Competence.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Notation/Competence.php'
    AND profile_id=1);
-- Teacher
INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 2, 'Notation/Competence.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Notation/Competence.php'
    AND profile_id=2);

-- Saisie des Absences -------------------------
-- Admin
INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'Notation/Absences.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Notation/Absences.php'
    AND profile_id=1);

-- Saisie du Prof. Principal -------------------------
-- Admin
INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'Notation/ProfPrincipal.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Notation/ProfPrincipal.php'
    AND profile_id=1);

-- Production des bulletins -------------------------
-- Admin
INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'Notation/Bulletins.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Notation/Bulletins.php'
    AND profile_id=1);

