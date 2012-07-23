SUBDIRS = $(shell ls | egrep '^[a-z]{2}_[A-Z]{2,4}' | tr '\n' ' ' )

files-list:
	@find ../ -type f -name '*.inc' | grep -v -F ".svn" >> files_php
	@find ../ -type f -name '*.php' | grep -v -F ".svn" >> files_php

	@find ../app/ -type f -name "*.tpl" | grep -v -F ".svn" >> files_smarty_templates

gettext: files-list
	@-xgettext -L PHP --from-code=UTF-8 -f files_php
#	CODEPAGE := $(shell echo ${LANG}.|cut -d. -f 2)
#	echo $(LANG)
	@cat files_smarty_templates|xargs ../scripts/tsmarty2c.php >> smarty.c
	@-xgettext --join-existing -L c --from-code=UTF-8 smarty.c

merge-all: $(addprefix merge-,$(SUBDIRS))

compile-all: $(addprefix compile-,$(SUBDIRS))

clear:
	@rm -f files_smarty_templates
	@rm -f files_php
	@rm -f smarty.c
	@rm -f messages.po

define lang-def
compile-$(1):
	@mv $(1)/LC_MESSAGES/messages.po $(1)/LC_MESSAGES/messages.po.orig
	msgconv -t UTF-8 $(1)/LC_MESSAGES/messages.po.orig -o $(1)/LC_MESSAGES/messages.po 2>/dev/null
	msgfmt $(1)/LC_MESSAGES/messages.po --output-file=$(1)/LC_MESSAGES/messages.mo
	@rm $(1)/LC_MESSAGES/messages.po
	@mv $(1)/LC_MESSAGES/messages.po.orig $(1)/LC_MESSAGES/messages.po

merge-$(1):
	@echo "Zpracovavam $(1)"
	-mv $(1)/LC_MESSAGES/messages.po $(1)/LC_MESSAGES/old.po;
	-cp messages.po $(1)/LC_MESSAGES;
	-msgmerge $(1)/LC_MESSAGES/old.po $(1)/LC_MESSAGES/messages.po --no-fuzzy-matching --output-file=$(1)/LC_MESSAGES/new.po
# -msgmerge $(1)/LC_MESSAGES/old.po $(1)/LC_MESSAGES/messages.po --output-file=$(1)/LC_MESSAGES/new.po
	-mv $(1)/LC_MESSAGES/new.po $(1)/LC_MESSAGES/messages.po
	-rm $(1)/LC_MESSAGES/old.po

endef

$(foreach lang, $(SUBDIRS),$(eval $(call lang-def,$(lang))))

