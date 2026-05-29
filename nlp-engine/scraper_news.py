import requests
from bs4 import BeautifulSoup
from datetime import datetime
import hashlib
import time
import re

# =========================================================
# SIGNALSTATE MULTI SOURCE INTELLIGENCE INGESTION ENGINE
# =========================================================

NEWS_SOURCES = [
    {
        "name": "Republika",
        "url": "https://www.republika.co.id/rss"
    },

    {
        "name": "Detikcom",
        "url": "https://feed.detik.com/rss/detikcom_news"
    },

    {
        "name": "Antara News",
        "url": "https://www.antaranews.com/rss/nasional.xml"
    },

    {
        "name": "CNN Indonesia",
        "url": "https://www.cnnindonesia.com/nasional/rss"
    },

    {
        "name": "Kompas",
        "url": "https://rss.kompas.com/rss"
    },

    {
        "name": "Tempo",
        "url": "https://rss.tempo.co/nasional"
    },

    {
        "name": "CNBC Indonesia",
        "url": "https://www.cnbcindonesia.com/rss"
    },
]

# =========================================================
# INTELLIGENCE KEYWORDS
# =========================================================

TARGET_KEYWORDS = {

    "politik": [
        "presiden",
        "menteri",
        "dpr",
        "pilkada",
        "pemilu",
        "kpu",
        "istana",
        "partai",
        "anies",
        "prabowo",
        "ganjar",
        "jokowi",
        "politik",
        "kabinet"
    ],

    "ekonomi": [
        "bbm",
        "inflasi",
        "rupiah",
        "subsidi",
        "sembako",
        "ekonomi",
        "bansos",
        "harga",
        "pajak",
        "bank indonesia",
        "investasi",
        "pasar",
        "kemiskinan",
        "pengangguran"
    ],

    "keamanan": [
        "polisi",
        "tni",
        "demo",
        "kerusuhan",
        "teror",
        "cyber",
        "hacker",
        "serangan",
        "kejahatan",
        "narkoba",
        "kriminal",
        "pembunuhan",
        "penipuan"
    ],

    "sosial": [
        "viral",
        "warga",
        "pendidikan",
        "kesehatan",
        "banjir",
        "korupsi",
        "bencana",
        "isu sosial",
        "masyarakat",
        "kemanusiaan"
    ]
}

# =========================================================
# CACHE MEMORY
# =========================================================

PROCESSED_HASHES = set()

# =========================================================
# CLEAN TEXT
# =========================================================

def clean_html(raw_html):

    clean = BeautifulSoup(raw_html, "html.parser").get_text()

    clean = re.sub(r"\s+", " ", clean)

    return clean.strip()

# =========================================================
# NLP ENGINE REQUEST
# =========================================================

def hit_nlp_engine(text):

    fastapi_url = "http://127.0.0.1:8001/analyze"

    try:

        response = requests.post(
            fastapi_url,
            json={"text": text},
            timeout=10
        )

        if response.status_code == 200:
            return response.json()

        print(f"[NLP WARNING] Status: {response.status_code}")

    except Exception as e:

        print(f"[NLP ERROR] {e}")

    # FALLBACK

    return {
        "sentiment": "neutral",
        "confidence_score": 0.5,
        "toxicity_score": 0.0,
        "emotion": "neutral",
        "cluster_name": "General Issue",
        "summary": "Automatic summary unavailable."
    }

# =========================================================
# PRIORITY CALCULATION
# =========================================================

def calculate_priority(nlp, match_score):

    score = 0

    if nlp["sentiment"] == "negative":
        score += 2

    if nlp["toxicity_score"] >= 0.5:
        score += 3

    if match_score >= 3:
        score += 2

    if score >= 5:
        return "high"

    if score >= 3:
        return "medium"

    return "low"

# =========================================================
# SEND TO LARAVEL
# =========================================================

def send_to_laravel(payload):

    laravel_url = "http://127.0.0.1:8000/api/posts"

    headers = {
        "Content-Type": "application/json"
    }

    try:

        response = requests.post(
            laravel_url,
            json=payload,
            headers=headers,
            timeout=10
        )

        if response.status_code == 201:

            print("   [SUCCESS] Saved to database")

            return True

        print(f"   [FAILED] Laravel rejected payload ({response.status_code})")

    except Exception as e:

        print(f"   [LARAVEL ERROR] {e}")

    return False

# =========================================================
# KEYWORD ANALYSIS
# =========================================================

def analyze_keywords(text):

    matched_category = None

    matched_keywords = []

    match_score = 0

    for category, keywords in TARGET_KEYWORDS.items():

        for keyword in keywords:

            if keyword.lower() in text:

                match_score += 1

                matched_keywords.append(keyword)

                if matched_category is None:
                    matched_category = category

    return {
        "category": matched_category,
        "keywords": matched_keywords,
        "score": match_score
    }

# =========================================================
# MAIN FETCH FUNCTION
# =========================================================

def fetch_multi_source_news():

    print("\n========================================================")
    print(f"[{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}]")
    print(" SIGNALSTATE INTELLIGENCE INGESTION ACTIVE ")
    print("========================================================")

    total_new = 0

    total_skipped = 0

    for source in NEWS_SOURCES:

        print(f"\n[SCANNING] {source['name']}")

        try:

            response = requests.get(
                source["url"],
                headers={
                    "User-Agent": "Mozilla/5.0"
                },
                timeout=15
            )

            if response.status_code != 200:

                print(f"   [FAILED] HTTP {response.status_code}")

                continue

            soup = BeautifulSoup(response.content, features="xml")

            items = soup.find_all("item")

            print(f"   [INFO] {len(items)} articles detected")

            for item in items:

                title = item.title.text if item.title else ""

                description = item.description.text if item.description else ""

                link = item.link.text if item.link else ""

                pub_date = item.pubDate.text if item.pubDate else ""

                clean_description = clean_html(description)

                full_content = f"{title}. {clean_description}"

                full_content_lower = full_content.lower()

                # =========================================================
                # FILTER SHORT CONTENT
                # =========================================================

                if len(full_content_lower) < 120:
                    continue

                # =========================================================
                # HASH DEDUPLICATION
                # =========================================================

                content_hash = hashlib.md5(
                    full_content_lower.encode()
                ).hexdigest()

                if content_hash in PROCESSED_HASHES:

                    total_skipped += 1

                    continue

                # =========================================================
                # KEYWORD ANALYSIS
                # =========================================================

                keyword_analysis = analyze_keywords(
                    full_content_lower
                )

                # =========================================================
                # RELEVANCE FILTER
                # =========================================================

                if keyword_analysis["score"] < 2:
                    continue

                # =========================================================
                # NLP ENGINE
                # =========================================================

                print(f"\n   [MATCH] {title[:80]}")

                print(f"   [CATEGORY] {keyword_analysis['category']}")

                print(f"   [KEYWORDS] {', '.join(keyword_analysis['keywords'])}")

                nlp = hit_nlp_engine(full_content)

                # =========================================================
                # PRIORITY
                # =========================================================

                priority = calculate_priority(
                    nlp,
                    keyword_analysis["score"]
                )

                # =========================================================
                # PAYLOAD
                # =========================================================

                payload = {

                    "platform": "News Portal",

                    "external_post_id": link,

                    "username": source["name"],

                    "display_name": source["name"],

                    "content": full_content,

                    "post_url": link,

                    "posted_at": pub_date,

                    # NLP

                    "sentiment": nlp.get(
                        "sentiment",
                        "neutral"
                    ),

                    "confidence_score": nlp.get(
                        "confidence_score",
                        0.5
                    ),

                    "toxicity_score": nlp.get(
                        "toxicity_score",
                        0.0
                    ),

                    "emotion": nlp.get(
                        "emotion",
                        "neutral"
                    ),

                    "cluster_name": nlp.get(
                        "cluster_name",
                        "General Issue"
                    ),

                    "summary": nlp.get(
                        "summary",
                        "-"
                    ),

                    # INTELLIGENCE

                    "issue_category": keyword_analysis["category"],

                    "matched_keywords": ", ".join(
                        keyword_analysis["keywords"]
                    ),

                    "priority_level": priority,

                    "match_score": keyword_analysis["score"]
                }

                # =========================================================
                # SEND TO BACKEND
                # =========================================================

                if send_to_laravel(payload):

                    PROCESSED_HASHES.add(content_hash)

                    total_new += 1

                    print(f"   [PRIORITY] {priority.upper()}")

                    print(f"   [SENTIMENT] {payload['sentiment']}")

        except Exception as e:

            print(f"\n[CRITICAL ERROR] {source['name']}")

            print(e)

    # =========================================================
    # FINAL SUMMARY
    # =========================================================

    print("\n========================================================")
    print(" INGESTION CYCLE COMPLETED ")
    print("========================================================")

    print(f"New Articles     : {total_new}")

    print(f"Skipped Duplicate: {total_skipped}")

# =========================================================
# MAIN LOOP
# =========================================================

if __name__ == "__main__":

    print("========================================================")
    print(" SIGNALSTATE INTELLIGENCE ENGINE ")
    print("========================================================")

    INTERVAL_SECONDS = 60

    try:

        while True:

            fetch_multi_source_news()

            print(f"\n[SLEEP] Waiting {INTERVAL_SECONDS} seconds...\n")

            time.sleep(INTERVAL_SECONDS)

    except KeyboardInterrupt:

        print("\n[STOPPED] Intelligence ingestion terminated.")