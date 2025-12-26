import Head from "next/head";
import PropTypes from "prop-types";
import { useRouter } from "next/router";
import DynamicFavicon from "../favicon/DynamicFavicon";

const SEO = ({
  title,
  description,
  keywords,
  image,
  businessName,
  configData,
}) => {
  const router = useRouter();
  const { asPath } = router;

  const siteName = configData ? businessName : "Loading"; // Replace with your website's name
  const siteUrl = "we"; // Replace with your website's URL

  // Concatenate the current page URL with the site URL
  const url = `${siteUrl}${asPath}`;

  const safeImage =
    image && !image.includes("null/null")
      ? image
      : configData?.fav_icon_full_url || "/favicon.ico";
  return (
    <>
      <DynamicFavicon configData={configData} />
      <Head>
        {/* General meta tags */}
        <title>{title ? `${title} | ${siteName}` : siteName}</title>
        <meta itemProp="name" content={title} />
        <meta itemProp="description" content={description} />
        {safeImage && <meta itemProp="image" content={safeImage} />}
        <meta property="og:type" content="website" />

        {/* Open Graph meta tags for Facebook */}
        <meta property="og:title" content={title || siteName} />
        <meta property="og:description" content={description} />
        <meta property="og:url" content={url} />
        <meta property="og:site_name" content={siteName} />
        {safeImage && <meta property="og:image" content={safeImage} />}

        {/* Twitter Card meta tags */}
        <meta name="twitter:title" content={title || siteName} />
        <meta name="twitter:description" content={description} />
        <meta name="twitter:url" content={url} />
        <meta name="twitter:card" content="summary_large_image" />
        {safeImage && <meta name="twitter:image" content={safeImage} />}

        {/* Google specific meta tags */}
        <meta itemProp="name" content={title || siteName} />
        <meta itemProp="description" content={description} />
        {safeImage && <meta itemProp="image" content={safeImage} />}

        <link rel="canonical" href={url} />
      </Head>
    </>
  );
};

SEO.propTypes = {
  title: PropTypes.string,
};

export default SEO;
