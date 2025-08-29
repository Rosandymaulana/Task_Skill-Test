import requests
from bs4 import BeautifulSoup
from requests.exceptions import RequestException

BASE_URL = "https://www.detik.com/search/searchall"


def scrape_article_body(url, headers):
    try:
        response = requests.get(url, headers=headers, timeout=10)
        response.raise_for_status()
        soup = BeautifulSoup(response.text, "html.parser")

        body_div = soup.find("div", class_="detail__body-text")
        if not body_div:
            return None
        
        paragraphs = []
        for p in body_div.find_all("p"):
            text = p.get_text(strip=True)
            if text and "SCROLL TO CONTINUE" not in text.upper():
                paragraphs.append(text)
        return " ".join(paragraphs) if paragraphs else None

    except Exception as e:
        print(f"[WARNING] Gagal ambil body dari {url}: {e}")
        return None


def scrape_detik(query, max_pages=3):
    results = []
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
                      "(KHTML, like Gecko) Chrome/118.0 Safari/537.36"
    }

    try:
        for page in range(1, max_pages + 1):
            params = {"query": query, "page": page}
            response = requests.get(BASE_URL, params=params, headers=headers, timeout=10)
            response.raise_for_status()

            soup = BeautifulSoup(response.text, "html.parser")
            articles = soup.find_all("article", class_="list-content__item")

            if not articles:
                print(f"[INFO] Tidak ada artikel di halaman {page}")
                break

            for art in articles:
                try:
                    # judul & link
                    title_tag = art.find("h3", class_="media__title")
                    link_tag = title_tag.find("a") if title_tag else None
                    title = link_tag.get_text(strip=True) if link_tag else None
                    link = link_tag["href"] if link_tag else None

                    # gambar
                    img_tag = art.find("img")
                    img = img_tag["src"] if img_tag else None

                    # waktu
                    time_tag = art.find("div", class_="media__date")
                    span_tag = time_tag.find("span") if time_tag else None
                    pub_time = span_tag["title"] if span_tag and span_tag.has_attr("title") else (
                        span_tag.get_text(strip=True) if span_tag else None
                    )

                    # body
                    body = scrape_article_body(link, headers) if link else None

                    if title and link:
                        results.append({
                            "title": title,
                            "image": img,
                            "body": body,
                            "publication_time": pub_time
                        })
                except Exception as e:
                    print(f"[WARNING] Gagal parse artikel: {e}")

        return results

    except RequestException as e:
        print(f"[ERROR] Network error: {e}")
        return []


if __name__ == "__main__":
    query = input("Masukkan kata kunci pencarian: ")
    data = scrape_detik(query, max_pages=1)

    for idx, item in enumerate(data, start=1):
        print(f"\nResult {idx}:")
        print(f"Title: {item['title']}")
        print(f"Image: {item['image']}")
        print(f"Publication Time: {item['publication_time']}")
        print(f"Body: {item['body']}")