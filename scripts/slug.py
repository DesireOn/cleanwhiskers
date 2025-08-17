import re
import unicodedata


def slugify(text: str) -> str:
    """Return a filesystem-friendly slug for *text*.

    Normalizes unicode characters, lowercases the string, and replaces any
    non-alphanumeric characters with single hyphens. Leading and trailing
    hyphens are stripped.
    """
    normalized = unicodedata.normalize("NFKD", text)
    ascii_text = normalized.encode("ascii", "ignore").decode("ascii")
    slug = re.sub(r"[^a-zA-Z0-9]+", "-", ascii_text).strip("-")
    return slug.lower()
