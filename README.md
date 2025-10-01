# Google Drive API Video Player - Advanced PHP

🚀 Google Drive API key kullanan gelişmiş video oynatıcısı sistemi. Admin paneli, çoklu API key desteği ve subtitle özelliği ile.

## ✨ Özellikler

### 🔑 API Key Yönetimi
- ✅ **Çoklu API Key Desteği**: Birden fazla API key ekleyebilir, biri bloklandığında otomatik diğerine geçer
- ✅ **API Key Rotasyonu**: Otomatik key değiştirme sistemi
- ✅ **Quota Monitoring**: API kullanım takibi

### 🎬 Video Player
- ✅ **Temiz Player**: Video başlığı ve bilgileri gizli
- ✅ **Gömme Desteği**: Başka sitelere embed edilebilir
- ✅ **Responsive**: Mobil uyumlu tasarım
- ✅ **Çoklu Format**: Tüm Google Drive video formatları

### 📝 Subtitle Sistemi
- ✅ **Admin Panel**: Videolara subtitle ekleme
- ✅ **Google Drive Subtitle**: Drive'dan .srt dosyası bağlama
- ✅ **Çoklu Dil**: Birden fazla subtitle desteği
- ✅ **Otomatik Algılama**: Subtitle dosyalarını otomatik bulma

### 🛡️ Admin Panel
- ✅ **Güvenli Giriş**: Şifreli admin sistemi
- ✅ **Video Yönetimi**: Video ekleme, düzenleme, silme
- ✅ **API Key Yönetimi**: Key ekleme, test etme, istatistikler
- ✅ **Subtitle Yönetimi**: Alt yazı dosyalarını yönetme

## 📁 Proje Yapısı

```
google-drive-api-video-player/
├── index.php                 # Ana giriş sayfası
├── config.php               # Yapılandırma dosyası
├── admin/                   # Admin panel
│   ├── index.php           # Admin ana sayfa
│   ├── login.php           # Admin girişi
│   ├── videos.php          # Video yönetimi
│   ├── apikeys.php         # API key yönetimi
│   └── subtitles.php       # Subtitle yönetimi
├── api/                     # API endpoint'leri
│   ├── video.php           # Video API
│   ├── subtitle.php        # Subtitle API
│   └── auth.php            # Kimlik doğrulama
├── player/                  # Video player'lar
│   ├── embed.php           # Gömme player'ı
│   ├── plyr.php            # Plyr player
│   ├── videojs.php         # Video.js player
│   └── jwplayer.php        # JW Player
├── assets/                  # Statik dosyalar
│   ├── css/
│   ├── js/
│   └── img/
├── data/                    # Veri dosyaları
│   ├── videos.json         # Video listesi
│   ├── apikeys.json        # API key'ler
│   └── subtitles.json      # Subtitle dosyaları
└── logs/                    # Log dosyaları
    ├── api.log
    └── error.log
```

## 🚀 Kurulum

### 1. Repository'yi İndirin
```bash
git clone https://github.com/anbarci/google-drive-api-video-player.git
cd google-drive-api-video-player
```

### 2. Gereksinimler
- PHP 7.4 veya üstü
- Apache/Nginx web sunucusu
- mod_rewrite aktif
- cURL extension
- JSON extension

### 3. Ayarlar
1. `config.php` dosyasını düzenleyin
2. Admin şifresini değiştirin
3. İlk Google Drive API key'inizi ekleyin

### 4. İzinler
```bash
chmod 755 data/
chmod 755 logs/
chmod 644 data/*.json
```

## 🔧 Google Drive API Key Alma

1. [Google Cloud Console](https://console.cloud.google.com/) açın
2. Yeni proje oluşturun veya mevcut projeyi seçin
3. "APIs & Services" > "Library" gidin
4. "Google Drive API"'yi etkinleştirin
5. "Credentials" > "Create Credentials" > "API Key"
6. Yeni API key'i kopyalayın
7. Admin panelinden API key'i ekleyin

## 📖 Kullanım

### Video Ekleme
1. Admin paneline giriş yapın (`/admin/`)
2. "Video Yönetimi" bölümüne gidin
3. Google Drive video linkini yapıştırın
4. Video bilgilerini doldurun
5. Kaydedin

### Subtitle Ekleme
1. Admin panelinde videoyu seçin
2. "Subtitle Ekle" butonuna tıklayın
3. Google Drive'dan .srt dosyası linkini ekleyin
4. Dil kodunu belirtin (tr, en, de, vb.)
5. Kaydedin

### Player Gömme
```html
<iframe src="https://yoursite.com/player/embed.php?id=VIDEO_ID" 
        width="800" height="450" frameborder="0" allowfullscreen>
</iframe>
```

## 🔑 API Key Rotasyonu

Sistem otomatik olarak:
- API quota dolduğunda sonraki key'e geçer
- Hata alan key'i geçici olarak devre dışı bırakır
- Başarılı istekleri loglar
- Key performansını izler

## 🛡️ Güvenlik

- API key'ler şifreli saklanır
- Admin paneli CSRF korumalı
- Rate limiting sistemi
- SQL injection koruması
- XSS filtreleme

## 🌐 Desteklenen Player'lar

1. **Plyr** - Modern HTML5 player
2. **Video.js** - Profesyonel video player
3. **JW Player** - Gelişmiş özellikler
4. **Custom HTML5** - Basit player

## 📊 İstatistikler

- Video izlenme sayıları
- API kullanım istatistikleri
- Hata logları
- Performans metrikleri

## 🔄 API Endpoint'leri

- `GET /api/video.php?id=VIDEO_ID` - Video bilgileri
- `GET /api/subtitle.php?id=VIDEO_ID&lang=tr` - Subtitle dosyası
- `POST /api/auth.php` - Admin kimlik doğrulama

## 🤝 Katkıda Bulunma

1. Fork edin
2. Feature branch oluşturun
3. Commit yapın
4. Pull request gönderin

## 📄 Lisans

MIT License - Detaylar için `LICENSE` dosyasına bakın.

---

**Geliştirici**: [@anbarci](https://github.com/anbarci)
**Versiyon**: 2.0.0
**Güncelleme**: Ekim 2025
