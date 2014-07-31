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
 * Strings for component 'tool_createusers', language 'en', branch 'MOODLE_22_STABLE'
 *
 * @package    tool
 * @subpackage createusers
 * @copyright  2014 Gordon Bateson {@link http://quizport.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// essential strings
$string['pluginname'] = 'Create anonymous users';

// more strings
$string['alternatenametype'] = 'Alternate name type';
$string['cancelenrolments'] = 'Cancel enrolments';
$string['cancelroles'] = 'Cancel role assignments';
$string['countlowercase'] = 'Lower case characters';
$string['countnumeric'] = 'Numeric characters';
$string['countuppercase'] = 'UPPER case characters';
$string['countusers'] = 'Number of users';
$string['defaultalternatename'] = 'anon';
$string['defaultdescription'] = 'Hi';
$string['defaultfirstname'] = 'First';
$string['defaultlastname'] = 'LAST';
$string['defaults'] = 'Defaults';
$string['defaultusernameprefix'] = 'user';
$string['doublebyte'] = 'Double-byte names';
$string['doublebyte_help'] = 'This setting affects the names of courses created by this tool.

**Yes**
: courses will be given the same name as the teacher\'s username, except that any single-byte numbers, letters and punctuation will be converted to their double-byte equivalents.

**No**
: courses will be given the exactly same name as the teacher\'s username';
$string['enrolcategory'] = 'Course category';
$string['enrolcategory_help'] = 'The course cateory to which teacher courses will be added';
$string['enrolcourses'] = 'Enrol in these courses';
$string['enrolgroups'] = 'Enrol in these groups';
$string['enrolstudents'] = 'Enrol students';
$string['enrolstudents_help'] = 'Any users selected here will be enrolled as students in each teacher course that this tool creates.';
$string['firstnametype'] = 'First name type';
$string['fixed'] = 'Fixed';
$string['folderpath'] = 'Course files folder';
$string['folderpath_help'] = 'This setting defines the path to a folder in a file system repository to which each course wil be given access.

File system repositories in each course can be enabled as follows:

1. create a folder on the server\'s file system
  * e.g. /path/to/moodle/data/repository/sitefiles
  * see also [Moodle docs: File system repository](http://docs.moodle.org/27/en/File_system_repository)
2. navigate to the repository overview page in your Moodle site
  * Administration -> Site administration -> Plugins -> Repositories -> Manage repositories
3. enable "File system" repository
  * **Active**: set to "Enabled and visible"
  * **Order**: use up arrows to move "File system" to top row
  * **Settings**: click this link and proceed to step 4 below
4. setup "File system" repository instance
  * enable both checkboxes to allow admins to add repositories to courses and for personal use
  * click button "Create a repository instance"
  * enter name, e.g. Site files, and click button "Save"';
$string['incrementusers'] = 'Increment';
$string['lastnametype'] = 'Last name type';
$string['names'] = 'Names';
$string['pageheader'] = 'Create and enrol anonymous users';
$string['passwords'] = 'Passwords';
$string['passwordtype'] = 'Password type';
$string['prefix'] = 'Prefix';
$string['resetcourses'] = 'Reset courses';
$string['resetcourses_help'] = 'This setting affects labels, resources and activities in any course that is re-used as a teacher course.

**Yes**
: all labels, resources and activities in re-used courses will be removed.

**No**
: labels, resources and activities in re-used courses will be left as they are.

Note that newly created courses are not affected by this setting.';
$string['resetgrades'] = 'Reset grades';
$string['showalternatename'] = 'Show alternate name';
$string['shownewuser'] = 'Show new user';
$string['showuserid'] = 'Show user id';
$string['shufflerandom'] = 'Shuffle';
$string['startusers'] = 'Start sequence';
$string['stringseparator'] = '.';
$string['studentcourses'] = 'Student course(s)';
$string['studentenrolments'] = 'Student Enrolments';
$string['studentgroups'] = 'Student group(s)';
$string['suffix'] = 'Suffix';
$string['teachercourse'] = 'Teacher course';
$string['teacherenrolments'] = 'Teacher Enrolments';
$string['typefixed'] = 'Same for all users';
$string['typerandom'] = 'Random';
$string['typesequence'] = 'Sequential';
$string['typeuserid'] = 'Same as user id';
$string['typeusername'] = 'Same as username';
$string['userlogindetails'] = 'User login details';
$string['userlogindetailsgroup'] = 'User login details: {$a}';
$string['usernameprefix'] = 'Username prefix';
$string['usernames'] = 'Usernames';
$string['usernamesuffix'] = 'Username suffix';
$string['usernametype'] = 'Numeric type';
$string['usernamewidth'] = 'Numeric width';
