# Image Thumbnail Helper

Create a thumbnail of a certain size for a local image file.  Files are cached.  The cache is only updated if the local source file is updated.

This method will automatically determine the proper image processing library to use (ImageMagick, GD, GD2, etc.).  To specify this manually, you will need to [specify this in your configuration file](/docs/configuration/advanced.md).

Images will be resized so that their proportions are retained.  In this way, you are really specifying the **maximum** height/width when calling this function, as only one of those dimensions may reach that maximum size.  The other will be controlled for proportions.

## Reference

## `string image_thumb (string $image_path , int $height , int $width)`

Returns a URL to to the cached image thumbnail generated with this function.

```
$file = '/home/mysite/public_html/writeable/images/my_image.jpg';
$this->load->helper('image_thumb');
$image_url = image_thumb($file, 100, 100);

echo '<img src="' . $image_url . '" alt="my image" />';
```

Output:

```
<img src="http://www.example.com/writeable/image_thumbs/8415d6a054ca3e1d5cfca150d42126c8.jpg" alt="my image" />
```