.SILENT:
all:
	php init.php | tee last.log
	cat last.log | ansi2txt > temp.log
	mv temp.log last.log
setup:
	sudo apt install colorized-logs
