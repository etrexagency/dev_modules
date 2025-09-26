# ETX: DEV Modules

## Description

DEV Modules facilitates the secure development and testing of REDAXO modules during operation. You can create a DEV copy for each LIVE module, use it specifically in the frontend, and overwrite the LIVE module with it if necessary—without compromising the public site. In addition, the add-on offers visual module management, which is handled by calls to the backend. This module management allows you to manage modules from the frontend and add them at any location. There is also a button for displaying the DEV copy instead of the LIVE module, if a copy exists. The page also has a button that takes you directly to edit mode.

## Functions

### Module management

- Create DEV copy: creates a second module with the suffix "[DEV]" for a LIVE module.
- Overwrite LIVE with DEV: transfers the current DEV status to the LIVE module.
- Delete DEV copy: removes the DEV variant and its assignment.

<img width="983" height="742" alt="image" src="https://github.com/user-attachments/assets/5e90afe5-9003-4164-bc88-aeebfe00d05b" />

### Frontend options (WYSIWYG tools)

Helpful controls can be displayed in the frontend—only for logged-in REDAXO users:

- Edit page: Direct link to the edit mode of the current page.
- Options Mode: displays administration buttons for each slice/module:
- Open, edit, delete, change status (online/offline)
- Copy, cut (blocks)
- Move up/down
- Add block between existing modules
- DEV Mode: Renders the DEV copies in the frontend instead of the LIVE modules. Ideal for testing new outputs without affecting live visitors.

<img width="975" height="879" alt="image" src="https://github.com/user-attachments/assets/561d0a5b-586d-4f79-bfad-d2dca8620a2e" />

#### Frontend Options active in Settings (shows the buttons below when signes in: edit page, options Mode und DEV Mode)

<img width="1260" height="1187" alt="image" src="https://github.com/user-attachments/assets/80c3e09f-268b-480a-aab6-d26e7fafdd66" />

#### Frontend Options: Options Mode active (shows options based on addon settings)

<img width="1259" height="1198" alt="image" src="https://github.com/user-attachments/assets/91f432d6-f70a-4c66-b3b8-e519d283b1cd" />

#### Frontend Options: Options Mode - Add block

<img width="1263" height="1198" alt="image" src="https://github.com/user-attachments/assets/b3965102-00a2-4916-8963-e2472e6353b8" />

#### Frontend Options: DEV Mode active (shows linked DEV modules instead of the LIVE ones created in the addn settings)

<img width="1267" height="1195" alt="image" src="https://github.com/user-attachments/assets/5e47ee6c-e7e9-43c9-b765-0d159fd3420e" />


### Settings

- LIVE / DEV Modules: List of all modules with actions
  – Create DEV copy, overwrite LIVE module, delete DEV copy.
- Frontend options: Switches for “Edit page,” “Options Mode,” “DEV Mode,” and the individual buttons (Edit, Move, Copy, Cut, Status, Delete).

### Security & Permissions

- The frontend tools only appear for logged-in REDAXO users (backend session).
- Optionally, the output can be restricted to admins.
- AddOn permission: dev_modules[] (controllable for roles).

### Workflow example

1. In LIVE / DEV Module, click “Create DEV copy” for a module.
2. Edit and test the DEV module.
3. Activate DEV mode in the frontend → Page displays the DEV modules.
4. Is everything OK? → “Overwrite LIVE module”.
5. LIVE module is overwritten by DEV
6. The modules are cleaned up accordingly.

## Integration

To use the add-on, it must be installed and stored in the template `<?php echo getArticleContent(); ?>`, which checks how the article slices of the modules are processed based on the active mode.

## Requirements

- Redaxo version: 5.15.0
- [blOecks](https://github.com/FriendsOfREDAXO/bloecks) >= 4.0.0 for cut & copy
