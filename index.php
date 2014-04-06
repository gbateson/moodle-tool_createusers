<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Search and createusers strings throughout all texts in the whole database
 *
 * @package    tool_createusers
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);

require_once('../../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/lib/adminlib.php');

// for Moodle <=2.5, we must include the form file manually
require_once($CFG->dirroot.'/admin/tool/createusers/classes/form.php');

admin_externalpage_setup('toolcreateusers');

$form = new tool_createusers_form();

if ($form->is_cancelled()) {
    echo redirect(new moodle_url('/admin/index.php'));
}

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('pageheader', 'tool_createusers'));

if ($form->is_submitted() && $form->is_validated()) {
    echo $OUTPUT->box_start();
    $form->create_users();
    echo $OUTPUT->box_end();
}

$form->display();

echo $OUTPUT->footer();
