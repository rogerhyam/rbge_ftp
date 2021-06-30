<html>
    <head>
        <title>data.rbge.org.uk - summary docs</title>
    </head>
    <body>
        <h1>data.rbge.org.uk - summary docs</h1>
    
        <p>data.rbge.org.uk - tends to be used for data centric stuff. Particularly it is used for permanent or semi-permanent URI's that we might
            implement in a different way later</p>

        <h2>Slash 'herb'</h2>
        
        <p>URLs of the for http://data.rbge.org.uk/herb/&lt;barcode-number&gt; are permanent URIs used for herbarium specimens. Calls to these URIs are <strong>rewritten</strong> to /index.php?path=herb/&lt;barcode-number&gt; where the PHP script does content negotiation and:</p>
        <ul>
            <li>If the caller is judged to be human they are 303 'See Other' <strong>redirected</strong> to the herbarium catalogue web page for the specimen.</li>
            <li>If the caller is judged to be a non-human they are 303 'See Other' <strong>redirected</strong> to the rdf/herb.php?guid=&lt;full request URI&gt; script. This script return an RDF rendering of the specimen metadata.</li>
        </ul>
                
        <h2>Slash 'living'</h2>
        
        <p>This is a twin of slash 'herb'. URIs of the for http://data.rbge.org.uk/living/&lt;accession-number&gt; are permanent URIs used for living accessions. Calls to these URIs are <strong>rewritten</strong> to /index.php?path=living/&lt;accession-number&gt; where the PHP script does content negotiation and:</p>
        <ul>
            <li>If the caller is judged to be human they are 303 'See Other' <strong>redirected</strong> to the living collection catalogue web page for the accession.</li>
            <li>If the caller is judged to be a non-human they are 303 'See Other' <strong>redirected</strong> to the rdf/living.php?guid=&lt;full request URI&gt; script. This script return an RDF rendering of the accession metadata.</li>
        </ul>
        
        
        <h2>Slash 'taxa'</h2>
        
        <p>This is similar to slash herb and slash living but permits us to issue stable URIs for binomial named taxa. The implementation is currently provisional.</p>
        
        <p>All URIs of the form http://data.rbge.org.uk/taxa/&lt;Genus&gt;/&lt;species&gt; are redirected to the script service/taxa/index.php?genus=&lt;Genus&gt;&amp;species=&lt;species&gt; which generates a web page listing our living and herbarium specimens for that species.</p>
        
        <p>In the future this service might do proper content negotiation which resulted in either human readable or RDF based descriptions of all resources available at RBGE for that taxon.</p>
        
        <h2>Slash 'image'</h2>
        
        <p>URIs of the form http://data.rbge.org.uk/image/&lt;derived-image-id&gt;/&lt;box-size&gt; return images from the specimen image archive as described in the image_archive database.  The derived-image-id must be the id of an image in the image_archive.derived_images table with an image_type of JPG. The image will be sized to fit in a square of dimensions box-size.</p>
        
        <p>Images are resized to order so the first call for an image of a certain size may take 3 seconds. Subsequent calls are more or less instant.</p>
        
        <p>The script is throttled to provide a maximum box-size of 1000 pixels. If box-size is omitted the 800 will be used.</p>
        
        
        <h2>Slash 'service/dwca'</h2>
        
        <p>This space is used for generating and storing Darwin Core Archive format files that are picked up by GBIF and possibly others.</p>
        
        <p>Calling <a href="http://data.rbge.org.uk/service/dwca/generator.php">http://data.rbge.org.uk/service/dwca/generator.php</a> will cause a new pair of archives to be created. The script takes several minutes to run. By default it is called from the command line every day.</p>
        
        <p>The two Darwin Core Archive files created are found at:</p>
        <ul>
            <li><a href="http://data.rbge.org.uk/service/dwca/data/darwin_core.zip">http://data.rbge.org.uk/service/dwca/data/darwin_core.zip</a></li>
            <li><a href="http://data.rbge.org.uk/service/dwca/data/darwin_core_living.zip">http://data.rbge.org.uk/service/dwca/data/darwin_core_living.zip</a></li>
        </ul>
        
        
        <h2>Slash 'service/herb-images'</h2>
        
        <p>This space is similar to the dwca space but used for generating and storing an EOL transfer format file that contains details of all our herbarium images.</p>
        
        <p>Calling <a href="http://data.rbge.org.uk/service/herb-images/generator.php">http://data.rbge.org.uk/service/herb-images/generator.php</a> will cause the archive to be created. The script takes several minutes to run. By default it is called from the command line every day.</p>
        
        <p>The archive file created can be found at: <a href="http://data.rbge.org.uk/service/herb-images/data/rbge_herbarium_images.xml.zip">http://data.rbge.org.uk/service/herb-images/data/rbge_herbarium_images.xml.zip</a></p>
        
        
        <h2>Slash 'publication'</h2>
        
        <p>URIs used by the PDF publication thing - FIXME Martin to complete.</p>
        
    </body>
</html>