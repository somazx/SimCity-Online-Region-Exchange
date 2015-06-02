<?php
	
	function status_update($d) {
		echo $d."<br>";
	}

	class sc4 {
		var $d=array();							//command line options
		var $data=false;							//buffer to store the file contents while operated upon.
		var $png=array();							//image holder.
		var $version='0.1Beta';

		function sc4_extract_pngs() {
			if(!$this->data) return false;
			status_update('Extracting png images....');
			$s=$e=0;
			while(($s=strpos($this->data,chr(hexdec(89)).'PNG',$s))!==false) {
				$this->png[$i=sizeof($this->png)]=substr($this->data,$s,($e=strpos($this->data,'IEND',$s))-$s+4);
				status_update('Processing image '.($i+1).' [ '.strlen($this->png[$i]).' bytes ] ....');
				$fp=fopen($this->d['f'].$i.'.png',"wb");
				fwrite($fp,$this->png[$i]);
				fclose($fp);
				$s=$e;
			}
			status_update('Processed '.sizeof($this->png).' images.');
		}

		function sc4() {
			echo "\nsc4 extractor v{$this->version}\n";
			$this->d=getopt('f:');
			if ($this->d['f'] && file_exists($this->d['f'])) {
				status_update('Opening file '.$this->d['f']);
				$this->data=file_get_contents($this->d['f']);
				status_update('Done [ '.strlen($this->data).' bytes ]');
				$this->sc4_extract_pngs();
			} else {
				status_update('Could not open "'.$this->d['f'].'" for processing.');
			}
			echo "done!\n";
		}
	}

?>