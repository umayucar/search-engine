# Search Engine API

Bu proje, farklı içerik sağlayıcılardan (JSON ve XML) gelen verileri birleştirerek, kullanıcının arama sorgusuna göre en uygun içerikleri bulan, bunları gelişmiş puanlama algoritmasıyla sıralayan ve modern bir dashboard arayüzü ile sunan tam özellikli bir arama motoru API'sidir.

## 📸 Önizleme

<img width="1079" height="761" alt="search" src="https://github.com/user-attachments/assets/d7bc55b5-51fc-4f03-9325-21d9abd8d4a2" />

## 🎯 Dashboard Kullanımı

### Ana Özellikler
- **📊 İstatistik Paneli**: Toplam içerik, video/makale dağılımı, ortalama skor
- **📱 Responsive Kartlar**: Zengin metadata ile görsel içerik sunumu
- **⚡ Sync Butonu**: Provider verilerini anında güncelleme

### Arama İpuçları
- **Genel Arama**: `programming`, `docker`, `kubernetes`
- **Spesifik Arama**: `"Go Programming"` 
- **Tag Arama**: Etiketler otomatik olarak aranır
- **Kombinasyon**: Tür + arama + sıralama kombinasyonları

## 🏗️ Teknoloji Stack

| Kategori | Teknoloji | Versiyon |
|----------|-----------|----------|
| **Backend** | Laravel | 12.x |
| **Frontend** | React + TypeScript | 18.x |
| **Database** | Mysql | - |
| **Styling** | Tailwind CSS | 4.x |
| **Build Tool** | Vite | 7.x |
| **API Bridge** | Inertia.js | 2.x |

## 🚀 Hızlı Başlangıç

### Sistem Gereksinimleri
```bash
PHP >= 8.2
Node.js >= 18.0
Composer >= 2.0
Git
```

### ⚡ Tek Komutla Kurulum
```bash
# Repository'yi klonla ve kur
git clone https://github.com/umayucar/search-engine.git
cd search-engine
chmod +x setup.sh && ./setup.sh
```

### 📋 Manuel Kurulum

#### 1. Projeyi İndirin
```bash
git clone https://github.com/umayucar/search-engine.git
cd search-engine
```

#### 2. Backend Kurulumu
```bash
# Composer bağımlılıkları
composer install

# Environment dosyası
cp .env.example .env
php artisan key:generate

# Veritabanı kurulumu
php artisan migrate

# İlk veri yüklemesi
php artisan content:sync
```

#### 3. Frontend Kurulumu
```bash
# NPM bağımlılıkları
npm install

# Development build
npm run dev

#### 4. Uygulamayı Başlatın
```bash
# Backend server
php artisan serve

# Frontend dev server (ayrı terminal)
npm run dev
```

🎉 **Tebrikler!** Uygulamanız http://localhost:8000 adresinde çalışıyor.

## 🔧 API Dokümantasyonu

### 🔍 Arama Endpoint'i
```http
GET /api/search
```

**Query Parameters:**
| Parametre | Tip | Varsayılan | Açıklama |
|-----------|-----|------------|----------|
| `query` | string | - | Arama terimi |
| `type` | enum | - | `video` veya `article` |
| `sort` | enum | `relevance` | `relevance`, `date`, `popularity` |
| `order` | enum | `desc` | `asc` veya `desc` |
| `page` | integer | 1 | Sayfa numarası |
| `per_page` | integer | 10 | Sayfa başına öğe (max: 20) |

**Örnek İstek:**
```bash
curl "http://localhost:8000/api/search?query=programming&type=video&sort=popularity&page=1"
```

**Örnek Yanıt:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Advanced Go Concurrency Patterns",
      "type": "video",
      "score": 74.84,
      "views": 25000,
      "likes": 2100,
      "tags": ["programming", "advanced", "concurrency"],
      "published_at": "2024-03-14T15:30:00.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 3,
    "total": 42
  }
}
```

### 📊 İstatistik Endpoint'i
```http
GET /api/search/stats
```

### 🔄 Senkronizasyon Endpoint'leri
```http
POST /api/sync           # Manuel senkronizasyon
GET /api/sync/status     # Senkronizasyon durumu
```

## 🧮 Puanlama Algoritması Detayları

### Formül
```
Final Skor = (Temel Puan × İçerik Katsayısı) + Güncellik Puanı + Etkileşim Puanı
```

### Hesaplama Örnekleri

#### Video İçerik
```
Temel Puan = (25000 views / 1000) + (2100 likes / 100) = 25 + 21 = 46
İçerik Katsayısı = 1.5 (video için)
Güncellik Puanı = 3 (1 ay içinde)
Etkileşim Puanı = (2100 / 25000) × 10 = 0.84

Final Skor = (46 × 1.5) + 3 + 0.84 = 72.84
```

#### Makale İçerik
```
Temel Puan = 8 reading_time + (450 reactions / 50) = 8 + 9 = 17
İçerik Katsayısı = 1.0 (makale için)
Güncellik Puanı = 3 (1 ay içinde)
Etkileşim Puanı = (450 / 8) × 5 = 281.25

Final Skor = (17 × 1.0) + 3 + 281.25 = 301.25
```

### Veri Senkronizasyon Yöntemleri

#### Manuel Komut
```bash
php artisan content:sync
```

#### API Üzerinden
```bash
curl -X POST http://localhost:8000/api/sync
```

#### Dashboard Üzerinden
Dashboard'daki "Sync Data" butonunu kullanın.

#### Otomatik Senkronizasyon (Cron)
```bash
# crontab -e
0 */6 * * * cd /path/to/project && php artisan content:sync
```

## 📚 Ek Kaynaklar

### Dokümantasyon
- [Laravel Documentation](https://laravel.com/docs)
- [React TypeScript Guide](https://react-typescript-cheatsheet.netlify.app/)
- [Tailwind CSS Reference](https://tailwindcss.com/docs)
- [Inertia.js Guide](https://inertiajs.com/)


### FAQ

**Yeni provider nasıl eklerim?**
`AbstractProvider` sınıfını extend edin ve `parseData()` ile `mapToStandardFormat()` metodlarını implement edin.

**Scoring algoritmasını nasıl değiştirebilirim?**
`Content` modelindeki `calculateScore()` metodunu override edin.

**Frontend'i nasıl özelleştirebilirim?**
`resources/js/Components/` dizinindeki React bileşenlerini düzenleyin.
