<?php
	function fileDecode($inbuf, $Cname)
	{
		  /* length of data */
		  $inlen=strlen($inbuf);

		  $outlen	=	unpack1($inbuf[6],'C') << 16;
		  $outlen	+=	unpack1($inbuf[7],'C') << 8;
		  $outlen	+=	unpack1($inbuf[8],'C');

		  /* position in file */
		  $inpos=9;
		  $outpos=0;

		  $outbuf =array();

		  while (($inpos<$inlen)&&(unpack1($inbuf[$inpos],'C')<0xFC))
			{


				 $packcode=$inbuf[$inpos];
				 $packcode=unpack1($packcode,'C');
				 $a=unpack1($inbuf[$inpos+1],'C');
				 $b=unpack1($inbuf[$inpos+2],'C');
				 //decho("inlen: {$inlen} | outlen: {$outlen} | inpos: {$inpos} | outpos: {$outpos} | packcode: $packcode");
				 
				 if (!($packcode&0x80)) {
					//decho('match 0x80');
					$lena=$packcode&3;
					mmemcpy($outbuf,$outpos,$inbuf,$inpos+2,$lena);
					$inpos+=$lena+2;
					$outpos+=$lena;
					$lenb=(($packcode&0x1c)>>2)+3;
					$offset=(($packcode>>5)<<8)+$a+1;
					mmemcpy($outbuf,$outpos,$outbuf,$outpos-$offset,$lenb);
					$outpos+=$lenb;
					//decho ("Code $packcode len plain: $lena len: $lenb offset: $offset outpos: $outpos");
				 }
				 else if (!($packcode&0x40)) {
					//decho('match 0x40');
					$lena=($a>>6)&3; 
					mmemcpy($outbuf,$outpos,$inbuf,$inpos+3,$lena);
					$inpos+=$lena+3;
					$outpos+=$lena;
					$lenb=($packcode&0x3f)+4;
					$offset=($a&0x3f)*256+$b+1;
					mmemcpy($outbuf,$outpos,$outbuf,$outpos-$offset,$lenb);
					$outpos+=$lenb;
					//decho ("Code $packcode len plain: $lena len: $lenb offset: $offset outpos: $outpos");  
				 }  
				 else if (!($packcode&0x20)) {
					//decho('match 0x20');
					$c=unpack1($inbuf[$inpos+3],'C');
					$lena=$packcode&3; 
					mmemcpy($outbuf,$outpos,$inbuf,$inpos+4,$lena);
					$inpos+=$lena+4;
					$outpos+=$lena;
					$lenb=(($packcode>>2)&3)*256+$c+5;
					$offset=(($packcode&0x10)<<12)+256*$a+$b+1;
					mmemcpy($outbuf,$outpos,$outbuf,$outpos-$offset,$lenb);
					$outpos+=$lenb;
					//decho ("Code $packcode len plain: $lena len: $lenb offset: $offset outpos: $outpos"); 
				 }  
				 else {
					//decho('match 0x1f');
					$len=($packcode&0x1f)*4+4;
					mmemcpy($outbuf,$outpos,$inbuf,$inpos+1,$len);
					$inpos+=$len+1;
					$outpos+=$len;
					//decho ("Code $packcode Plain Chars: $len outpos: $outpos"); //Code 224 Plain Chars: 4 outpos: 0
				 }
			}

		  /* trailing bytes 
		  if (($inpos<$inlen)&&($outpos<$outlen)) {
			 mmemcpy($outbuf,$outpos,$inbuf,$inpos+1,unpack1($inbuf[$inpos],'C')&3);
			 $outpos+=unpack1($inbuf[$inpos],'C')&3;
		  }
		  */
		  
		  /*
		  if ($outpos!=$outlen) 
			  decho("Warning: bad length ? {$outpos} instead of {$outlen} with {$Cname}"); 
		  */
		  $buflen=$outlen;
		  return $outbuf;
	}



	function mmemcpy(&$dest, $destpos, &$src, $srcpos, $len)
		{
		  while ($len--)
			{
				//decho ("destpos: $destpos | srcpos: {$src[$srcpos]}");
				$dest[$destpos] = $src[$srcpos];

				$destpos++;
				$srcpos++;
			}
		}


	function unpack1($bytes, $type=false)
	{
		if($type)
		{
			$dat = unpack("{$type}int",$bytes);
			return $dat['int'];
		} else {
			$dat = unpack('Vint',$bytes);
			return $dat['int'];
		}
	}
?>