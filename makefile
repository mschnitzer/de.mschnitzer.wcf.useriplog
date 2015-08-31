.PHONY: all

TPLFILES := userProfileIPLog.tpl

all: release
	tar -C files/ -vcf files.tar lib
	tar -C templates/ -vcf templates.tar $(TPLFILES)
	tar -C updates/100alpha2/files/ -vcf files_update.tar lib
	mv files_update.tar updates/100alpha2/files.tar
	tar cfvz de.mschnitzer.wcf.useriplog.tar.gz *.xml *.tar *.sql languages updates/100alpha2/files.tar
	mv de.mschnitzer.wcf.useriplog.tar.gz release/
	rm *.tar

release:
	mkdir release
