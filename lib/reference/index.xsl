<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:atom="http://www.w3.org/2005/Atom"
	version="2.0">
	
	<xsl:output 
		method="html" 
		indent="yes" 
		doctype-system="html" 
		omit-xml-declaration="yes" 
		cdata-section-elements="atom:content"
		encoding="UTF-8" />

<!-- Main document-->
<xsl:template match="/">
<!-- Local testing in Firefox, set about:config value -
	security.fileuri.strict_origin_policy = false -->
<html>
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="style.css" />
<title><xsl:value-of select="atom:feed/atom:title"/></title>
<meta name="author" content="{//atom:author/atom:name}" />
<meta name="revised" content="{//atom:updated}" />
<meta name="DC.identifier" content="{//atom:id}" />
<meta name="DC.date" scheme="W3CDTF" content="{//atom:updated}" />
</head>
<body>
	<h1><xsl:value-of select="atom:feed/atom:title" /></h1>
	
	<xsl:for-each select="atom:feed/atom:entry">
		<xsl:apply-templates select="." />
	</xsl:for-each>
	
	<footer>
		<nav class="pages">
			<ul>
				<li><a href="{//atom:link[@rel='first']/@href}">First</a></li>
				<li><a href="{//atom:link[@rel='previous']/@href}">Previous</a></li>
				<li><a href="{//atom:link[@rel='self']/@href}#top">Up</a></li>
				<li><a href="{//atom:link[@rel='next']/@href}">Next</a></li>
				<li><a href="{//atom:link[@rel='last']/@href}">Last</a></li>
			</ul>
		</nav>
	</footer>
</body>
</html>
</xsl:template>

<!-- Entry template -->
<xsl:template match="atom:entry">
<article>
	<header>
		<h2><xsl:value-of select="atom:title" /></h2>
		<p><datetime><xsl:value-of 
			select="atom:published" /></datetime></p>
	</header>
	<xsl:choose>
		<!-- Priority to summary, if present -->
		<xsl:when test="atom:summary">
			<p><xsl:value-of select="atom:summary" /></p>
		</xsl:when>
		<xsl:when test="atom:content">
			<xsl:value-of disable-output-escaping="yes" 
				select="atom:content" />
		</xsl:when>
	</xsl:choose>
</article>
</xsl:template>

</xsl:stylesheet>
