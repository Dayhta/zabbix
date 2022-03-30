/*
** Zabbix
** Copyright (C) 2001-2022 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

#ifndef ZBX_USER_MACRO_H
#define ZBX_USER_MACRO_H

#include "common.h"
#include "zbxalgo.h"
#include "dbsync.h"

typedef struct
{
	zbx_uint64_t	macroid;
	zbx_uint64_t	hostid;
	const char	*name;
	const char	*context;
	const char	*value;
	zbx_uint32_t	refcount;
	unsigned char	type;
	unsigned char	context_op;
}
zbx_um_macro_t;

ZBX_PTR_VECTOR_DECL(um_macro, zbx_um_macro_t *)

typedef struct
{
	zbx_uint64_t		hostid;
	zbx_vector_uint64_t	templateids;
	zbx_vector_um_macro_t	macros;
	zbx_uint32_t		refcount;
}
zbx_um_host_t;

typedef struct
{
	zbx_hashset_t	hosts;
	zbx_uint32_t	refcount;
}
zbx_um_cache_t;

zbx_hash_t	um_macro_hash(const void *d);
int	um_macro_compare(const void *d1, const void *d2);

zbx_um_cache_t	*um_cache_create();
void	um_cache_release(zbx_um_cache_t *cache);
void	um_macro_release(zbx_um_macro_t *macro);

zbx_um_cache_t	*um_cache_sync(zbx_um_cache_t *cache, zbx_dbsync_t *gmacros, zbx_dbsync_t *hmacros,
		zbx_dbsync_t *htmpls);

#endif
