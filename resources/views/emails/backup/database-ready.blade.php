<x-mail::message>
# Daily Database Backup Ready

Your scheduled database backup has been created successfully.

## Backup Details

**Filename:** {{ $filename }}
**File Size:** {{ $fileSize }}
**Created:** {{ $createdAt }}
**Expires:** {{ $expiresAt }}

<x-mail::button :url="$downloadUrl">
Download Backup
</x-mail::button>

**Important:** This download link will expire in 24 hours. Please download your backup before the link expires.

---

This is an automated backup notification from your AccMarket system.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
