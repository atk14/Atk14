SUBDIRS = $(shell { ls | cat; } | egrep '^[a-z]{2}_[A-Z]{2,4}' | sort | uniq | tr '\n' ' ' )

pot: files_php
	-xgettext -L PHP --from-code=UTF-8 -f $< --output=- > atk14.pot
	@sed --in-place atk14.pot --expression='s/Content-Type: text\/plain; charset=CHARSET/Content-Type: text\/plain; charset=UTF-8/'
	@sed --in-place atk14.pot --expression='s/Project-Id-Version: PACKAGE VERSION/Project-Id-Version: ATK14 Framework/'
	@sed --in-place atk14.pot --expression='s/Language-Team: LANGUAGE <LL@li.org>/Language-Team: ATK14 Development Team/'
	@rm $<

files_php:
	@echo "clean list of php files"
	> $@
	find ../ -type f -name '*.php' | egrep -v "(/test/|/src/smarty)" | sort >> $@

merge: $(addprefix merge-,$(SUBDIRS))

test: $(addprefix test-,$(SUBDIRS))

clear:
	@rm -f files_php

define lang-def
merge-$(1):
	@echo "----> Merging at $(1)"

	mkdir -p $(1)/LC_MESSAGES

	if [ ! -f  $(1)/LC_MESSAGES/atk14.po ]; then cp atk14.pot $(1)/LC_MESSAGES/atk14.po; fi
	-mv $(1)/LC_MESSAGES/atk14.po $(1)/LC_MESSAGES/atk14.po.orig;

	# Extractions are being filtered out and msgcat helps us to remove blank lines at the end of file
	-msgmerge $(1)/LC_MESSAGES/atk14.po.orig atk14.pot --no-fuzzy-matching --output-file=- | egrep -v '^#~' | msgcat - > $(1)/LC_MESSAGES/atk14.po

	-rm $(1)/LC_MESSAGES/atk14.po.orig

test-$(1):
	@echo -n "----> Compiling at $(1): "
	@msgfmt $(1)/LC_MESSAGES/atk14.po --output-file=$(1)/LC_MESSAGES/atk14.mo
	@rm $(1)/LC_MESSAGES/atk14.mo
	@echo "Success"

endef

$(foreach lang, $(SUBDIRS),$(eval $(call lang-def,$(lang))))

