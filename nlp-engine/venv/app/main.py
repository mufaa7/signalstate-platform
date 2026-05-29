from fastapi import FastAPI
from pydantic import BaseModel
import re
from collections import Counter

# =========================================================
# SIGNALSTATE NLP ENGINE v4
# =========================================================

app = FastAPI(
    title="SignalState NLP Engine",
    description="Advanced Intelligence Analytics Engine",
    version="4.0"
)

# =========================================================
# REQUEST MODEL
# =========================================================

class PostPayload(BaseModel):
    text: str

# =========================================================
# TEXT CLEANER
# =========================================================

def clean_text(text):

    if not text:
        return ""

    text = text.lower()

    text = re.sub(r"http\\S+", "", text)

    text = re.sub(r"[^a-zA-Z0-9\\s]", " ", text)

    text = re.sub(r"\\s+", " ", text)

    return text.strip()

# =========================================================
# TOKENIZER
# =========================================================

def tokenize(text):

    return text.split()

# =========================================================
# SUMMARY ENGINE
# =========================================================

def generate_summary(text):

    sentences = re.split(r"[.!?]", text)

    for sentence in sentences:

        sentence = sentence.strip()

        if len(sentence.split()) >= 8:
            return sentence[:220] + "..."

    words = text.split()

    return " ".join(words[:25]) + "..."

# =========================================================
# ENTITY EXTRACTION
# =========================================================

def extract_entities(tokens):

    entities = []

    tracked_entities = [

        "jokowi",
        "prabowo",
        "anies",
        "ganjar",
        "dpr",
        "kpk",
        "polri",
        "tni",
        "jakarta",
        "indonesia",
        "pertamina",
        "mahkamah konstitusi",
        "kpu",
        "bank indonesia"
    ]

    for entity in tracked_entities:

        if entity in " ".join(tokens):
            entities.append(entity)

    return list(set(entities))

# =========================================================
# SENTIMENT ENGINE
# =========================================================

POSITIVE_WORDS = {

    "sukses",
    "berhasil",
    "apresiasi",
    "aman",
    "stabil",
    "membaik",
    "solusi",
    "pertumbuhan",
    "untung",
    "positif",
    "damai",
    "kondusif"
}

NEGATIVE_WORDS = {

    "korupsi",
    "krisis",
    "gagal",
    "demo",
    "mahal",
    "bocor",
    "tewas",
    "kerusuhan",
    "banjir",
    "kriminal",
    "utang",
    "inflasi",
    "maling",
    "pungli",
    "teror",
    "serangan",
    "bencana",
    "pemecatan"
}

TOXIC_WORDS = {

    "goblok",
    "tolol",
    "anjing",
    "bangsat",
    "ajg",
    "bodoh",
    "koruptor",
    "brengsek"
}

# =========================================================
# CLUSTER ENGINE
# =========================================================

CLUSTERS = {

    "Politik & Pemerintahan": [
        "presiden",
        "dpr",
        "kpu",
        "pilkada",
        "pemilu",
        "partai",
        "politik",
        "kabinet",
        "menteri"
    ],

    "Ekonomi & Harga": [
        "bbm",
        "inflasi",
        "harga",
        "rupiah",
        "subsidi",
        "sembako",
        "pajak",
        "ekonomi",
        "bansos"
    ],

    "Keamanan & Kriminal": [
        "polisi",
        "kriminal",
        "maling",
        "pembunuhan",
        "narkoba",
        "penipuan",
        "teror",
        "serangan"
    ],

    "Bencana & Infrastruktur": [
        "banjir",
        "gempa",
        "jalan",
        "macet",
        "transportasi",
        "bencana",
        "longsor"
    ],

    "Sosial & Publik": [
        "viral",
        "warga",
        "masyarakat",
        "pendidikan",
        "kesehatan",
        "kemiskinan"
    ]
}

# =========================================================
# CLUSTER DETECTION
# =========================================================

def detect_cluster(tokens):

    best_cluster = "General Public Issue"

    highest_score = 0

    for cluster_name, keywords in CLUSTERS.items():

        score = sum(1 for keyword in keywords if keyword in tokens)

        if score > highest_score:

            highest_score = score

            best_cluster = cluster_name

    return best_cluster

# =========================================================
# SENTIMENT ANALYSIS
# =========================================================

def analyze_sentiment(tokens):

    positive_score = sum(
        1 for token in tokens if token in POSITIVE_WORDS
    )

    negative_score = sum(
        1 for token in tokens if token in NEGATIVE_WORDS
    )

    toxic_score_raw = sum(
        1 for token in tokens if token in TOXIC_WORDS
    )

    # SENTIMENT

    if positive_score > negative_score:

        sentiment = "positive"

        emotion = "happy"

    elif negative_score > positive_score:

        sentiment = "negative"

        emotion = "angry" if toxic_score_raw > 0 else "sad"

    else:

        sentiment = "neutral"

        emotion = "neutral"

    # CONFIDENCE

    total = positive_score + negative_score

    if total == 0:
        confidence = 0.50
    else:
        confidence = round(
            max(positive_score, negative_score) / total,
            2
        )

    # TOXICITY

    toxicity = min(
        round(toxic_score_raw / 3, 2),
        1.0
    )

    return {
        "sentiment": sentiment,
        "emotion": emotion,
        "confidence_score": confidence,
        "toxicity_score": toxicity,
        "positive_score": positive_score,
        "negative_score": negative_score
    }

# =========================================================
# PRIORITY ENGINE
# =========================================================

def calculate_priority(sentiment_data, tokens):

    score = 0

    if sentiment_data["sentiment"] == "negative":
        score += 2

    if sentiment_data["toxicity_score"] >= 0.5:
        score += 3

    urgent_keywords = {

        "demo",
        "kerusuhan",
        "teror",
        "banjir",
        "gempa",
        "korupsi",
        "krisis",
        "mahal"
    }

    urgent_hits = sum(
        1 for token in tokens if token in urgent_keywords
    )

    score += urgent_hits

    if score >= 5:
        return "high"

    if score >= 3:
        return "medium"

    return "low"

# =========================================================
# TREND KEYWORD EXTRACTION
# =========================================================

def extract_top_keywords(tokens):

    stopwords = {

        "yang",
        "dan",
        "di",
        "ke",
        "dari",
        "untuk",
        "dengan",
        "adalah",
        "karena",
        "dalam",
        "itu",
        "ini"
    }

    filtered = [

        token for token in tokens

        if token not in stopwords and len(token) > 3
    ]

    counter = Counter(filtered)

    return [word for word, _ in counter.most_common(5)]

# =========================================================
# MAIN ANALYZE ENDPOINT
# =========================================================

@app.post("/analyze")
def analyze_data(payload: PostPayload):

    raw_text = payload.text

    cleaned = clean_text(raw_text)

    tokens = tokenize(cleaned)

    # =====================================================
    # ANALYSIS
    # =====================================================

    sentiment_data = analyze_sentiment(tokens)

    cluster_name = detect_cluster(tokens)

    summary = generate_summary(raw_text)

    entities = extract_entities(tokens)

    priority = calculate_priority(
        sentiment_data,
        tokens
    )

    top_keywords = extract_top_keywords(tokens)

    # =====================================================
    # OUTPUT
    # =====================================================

    return {

        "sentiment": sentiment_data["sentiment"],

        "confidence_score": sentiment_data["confidence_score"],

        "toxicity_score": sentiment_data["toxicity_score"],

        "emotion": sentiment_data["emotion"],

        "cluster_name": cluster_name,

        "summary": summary,

        "priority_level": priority,

        "entities": entities,

        "top_keywords": top_keywords,

        "positive_score": sentiment_data["positive_score"],

        "negative_score": sentiment_data["negative_score"]
    }

