<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
                xmlns:html="http://www.w3.org/TR/REC-html40"
                xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
    <xsl:template match="/">
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <title>XML Sitemap</title>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="robots" content="noindex,follow"/>
                <style type="text/css">
                    body, html {
                        margin: 0;
                        padding: 0;
                        background-color: #F2F2F2;
                    }

                    body {
                        font-family: Tahoma, Verdana, sans-serif;
                        font-size: 1em;
                    }

                    #header {
                        background-color: #a8b4ec;
                        border-bottom: 5px #4F5B93 solid;
                    }

                    #header h1 {
                        width: 80%;
                        margin: 0 auto;
                        padding: 1em 0;
                        letter-spacing: 0.08em;
                    }

                    #content {
                        font-family: Arial, Verdana, "Trebuchet MS", sans-serif;
                        margin: 1em;
                    }

                    #header p {
                        line-height: 2em;
                    }

                    table {
                        border-spacing: 0;
                        border-collapse: collapse;
                        margin: 2em auto;
                    }

                    td {
                        font-size: 1em;
                        padding: 0.5em 1em;
                    }

                    td:first-child {
                        font-size: 0.9em;
                        text-align: left;
                    }

                    td {
                        text-align: center;
                    }

                    th {
                        font-family: "Consolas", "Bitstream Vera Sans Mono", "Courier New", Courier, monospace;
                        text-align: center;
                        font-size: 1.2em;
                        border: none;
                        padding: 1em 0.8em;
                        letter-spacing: 0.01pt;
                    }

                    tr.stripe {
                        background-color: #fff;
                    }

                    tr {
                        border: none;
                    }

                    #footer {
                        padding: 2px;
                        margin-top: 10px;
                        font-size: 0.9em;
                        color: #808080;
                    }

                    #footer a {
                        color: #808080;
                    }

                    a {
                        color: #000000;
                    }
                </style>
            </head>
            <body>
                <xsl:apply-templates></xsl:apply-templates>
                <div id="footer">

                </div>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="sitemap:urlset">
        <div id="header">
            <h1>Sitemap</h1>
        </div>
        <div id="content">
            <table cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th>Sitemap URL</th>
                        <th>Priority</th>
                        <th>Change frequency</th>
                        <th>Last modified (UTC)</th>
                    </tr>
                </thead>
                <tbody>
                    <xsl:variable name="lower" select="'abcdefghijklmnopqrstuvwxyz'"/>
                    <xsl:variable name="upper" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
                    <xsl:for-each select="./sitemap:url">
                        <tr>
                            <xsl:if test="position() mod 2 != 1">
                                <xsl:attribute name="class">stripe</xsl:attribute>
                            </xsl:if>
                            <td>
                                <xsl:variable name="itemURL">
                                    <xsl:value-of select="sitemap:loc"/>
                                </xsl:variable>
                                <a href="{$itemURL}" target="_blank">
                                    <xsl:value-of select="sitemap:loc"/>
                                </a>
                            </td>
                            <td>
                                <xsl:value-of select="concat(sitemap:priority*100,'%')"/>
                            </td>
                            <td>
                                <xsl:value-of
                                        select="concat(translate(substring(sitemap:changefreq, 1, 1),concat($lower, $upper),concat($upper, $lower)),substring(sitemap:changefreq, 2))"/>
                            </td>
                            <td>
                                <xsl:value-of
                                        select="concat(substring(sitemap:lastmod,0,11),concat(' ', substring(sitemap:lastmod,12,5)))"/>
                            </td>
                        </tr>
                    </xsl:for-each>
                </tbody>
            </table>
        </div>
    </xsl:template>


    <xsl:template match="sitemap:sitemapindex">
        <div id="header">
            <h1>Sitemap Index</h1>

        </div>
        <div id="content">
            <table>
                <tr>
                    <th>URL</th>
                    <th>Last modified (GMT)</th>
                </tr>
                <xsl:for-each select="./sitemap:sitemap">
                    <tr>
                        <xsl:if test="position() mod 2 != 0">
                            <xsl:attribute name="class">stripe</xsl:attribute>
                        </xsl:if>
                        <td>
                            <xsl:variable name="itemURL">
                                <xsl:value-of select="sitemap:loc"/>
                            </xsl:variable>
                            <a href="{$itemURL}">
                                <xsl:value-of select="sitemap:loc"/>
                            </a>
                        </td>
                        <td>
                            <xsl:value-of
                                    select="concat(substring(sitemap:lastmod,0,11),concat(' ', substring(sitemap:lastmod,12,5)))"/>
                        </td>
                    </tr>
                </xsl:for-each>
            </table>
        </div>
    </xsl:template>
</xsl:stylesheet>