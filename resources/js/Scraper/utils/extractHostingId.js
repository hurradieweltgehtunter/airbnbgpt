export default function extractHostingId (url)
{
    const regex = /\/rooms\/(\d+)/;
    const match = url.match(regex);
    return match ? match[1] : null;
}

