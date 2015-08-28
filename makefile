.PHONY: all

TPLFILES := userProfileMultiAccounts.tpl

all: release
	tar -C files/ -vcf files.tar lib
	tar -C templates/ -vcf templates.tar $(TPLFILES)
	tar cfvz de.mschnitzer.wcf.useriplog.tar.gz *.xml *.tar *.sql languages
	mv de.mschnitzer.wcf.useriplog.tar.gz release/
	rm *.tar

release:
	mkdir release
