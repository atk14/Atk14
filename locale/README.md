Here are messages from the ATK14 framework localized to several languages using gettext.

Follow the following steps to update gettext files.

Generates atk14.pot file

    make pot

Merges atk14.pot with en_US/LC_MESSAGES/atk.po, cs_CZ/LC_MESSAGES/atk.po and so on.

    make merge

Now it is time to translate all new messages.

    poedit cs_CZ/LC_MESSAGES/atk14.po

Compile check. Checking whether all the atk14.po files can be compiled to "mo" format.

    make test

